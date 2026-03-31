<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeFollowup;
use App\Models\FollowupContact;
use App\Models\Module;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class GradeFollowupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:program.view'])->only(['show', 'history', 'showFollowup']);
        $this->middleware(['permission:program.create'])->only(['create', 'store', 'edit', 'update', 'addContact', 'storeContact', 'addRecovery', 'storeRecovery', 'close', 'createNew']);
    }

    /**
     * Mostrar el formulario para crear un nuevo seguimiento.
     */
    public function create(Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Verificar que la calificación no sea aprobatoria
        if ($grade->approved) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede hacer seguimiento a calificaciones no aprobatorias.'
                ]);
            }
            return redirect()->route('grades.summary', [$program->id, $module->id])
                ->with('error', 'Solo se puede hacer seguimiento a calificaciones no aprobatorias.');
        }

        // Obtener o inicializar el seguimiento
        $followup = $grade->followup ?? new GradeFollowup();

        // Si es una petición AJAX para modal, retornar solo el contenido
        if (request()->ajax() || request()->wantsJson()) {
            return view('grade_followups.create_modal_content', compact('program', 'module', 'grade', 'followup'));
        }

        return view('grade_followups.create', compact('program', 'module', 'grade', 'followup'));
    }

    /**
     * Almacenar un nuevo seguimiento.
     */
    public function store(Request $request, Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'observations' => 'nullable|string',
        ]);

        // Verificar si ya existe un seguimiento abierto
        $existingOpenFollowup = GradeFollowup::where('grade_id', $grade->id)
            ->where('status', 'open')
            ->first();

        if ($existingOpenFollowup) {
            // Si ya existe un seguimiento abierto, actualizarlo
            $existingOpenFollowup->update($validated);
            $followup = $existingOpenFollowup;
        } else {
            // Si no hay seguimiento abierto, crear uno nuevo
            $followup = GradeFollowup::create(array_merge($validated, [
                'grade_id' => $grade->id,
                'creator_id' => Auth::id(),
                'status' => 'open'
            ]));
        }

        // Si es una petición AJAX, retornar JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Seguimiento guardado correctamente.',
                'followup_id' => $followup->id
            ]);
        }

        return redirect()->route('grade_followups.show', [$program->id, $module->id, $grade->id])
            ->with('success', 'Seguimiento guardado correctamente.');
    }

    /**
     * Mostrar el seguimiento de una calificación.
     */
    public function show(Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Obtener todos los seguimientos de esta calificación
        $followups = GradeFollowup::where('grade_id', $grade->id)
            ->with('contacts')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Obtener el seguimiento activo (abierto) o el más reciente
        $followup = $followups->where('status', 'open')->first() ?? $followups->first();

        if (!$followup) {
            return redirect()->route('grade_followups.create', [$program->id, $module->id, $grade->id]);
        }

        // Obtener los contactos ordenados por fecha
        $calls = $followup->calls()->orderBy('contact_date', 'desc')->get();
        $messages = $followup->messages()->orderBy('contact_date', 'desc')->get();

        return view('grade_followups.show', compact('program', 'module', 'grade', 'followups', 'followup', 'calls', 'messages'));
    }    /**
     * Mostrar el formulario para agregar un nuevo contacto.
     */
    public function addContact(Program $program, Module $module, Grade $grade, $type)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Verificar que el tipo sea válido
        if (!in_array($type, ['call', 'message'])) {
            abort(404);
        }

        // Obtener o crear el seguimiento
        $followup = $grade->followup ?? GradeFollowup::create([
            'grade_id' => $grade->id,
            'creator_id' => Auth::id()
        ]);

        return view('grade_followups.add_contact', compact('program', 'module', 'grade', 'followup', 'type'));
    }

    /**
     * Almacenar un nuevo contacto.
     */
    public function storeContact(Request $request, Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Obtener el seguimiento abierto
        $followup = GradeFollowup::where('grade_id', $grade->id)
            ->where('status', 'open')
            ->first();

        if (!$followup) {
            abort(404);
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'type' => 'required|in:call,message',
            'contact_date' => 'required|date',
            'got_response' => 'boolean',
            'response_date' => 'nullable|date|required_if:got_response,1',
            'notes' => 'nullable|string',
        ]);

        // Crear el contacto
        $contact = new FollowupContact($validated);
        $contact->grade_followups_id = $followup->id;
        $contact->save();

        return redirect()->route('grade_followups.show', [$program->id, $module->id, $grade->id])
            ->with('success', 'Contacto registrado correctamente.');
    }

    /**
     * Eliminar un contacto.
     */
    public function deleteContact(Program $program, Module $module, Grade $grade, FollowupContact $contact)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Verificar que el contacto pertenezca al seguimiento
        if ($contact->grade_followups_id != $grade->followup->id) {
            abort(404);
        }

        // Eliminar el contacto
        $contact->delete();

        return redirect()->route('grade_followups.show', [$program->id, $module->id, $grade->id])
            ->with('success', 'Contacto eliminado correctamente.');
    }

    /**
     * Mostrar el formulario para agregar o editar un recuperatorio.
     */
    public function addRecovery(Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Obtener o crear el seguimiento
        $followup = $grade->followup ?? GradeFollowup::create([
            'grade_id' => $grade->id,
            'creator_id' => Auth::id()
        ]);

        return view('grade_followups.add_recovery', compact('program', 'module', 'grade', 'followup'));
    }

    /**
     * Almacenar o actualizar un recuperatorio.
     */
    public function storeRecovery(Request $request, Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Obtener el seguimiento abierto
        $followup = GradeFollowup::where('grade_id', $grade->id)
            ->where('status', 'open')
            ->first();

        if (!$followup) {
            abort(404);
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'has_recovery' => 'boolean',
            'recovery_start_date' => 'required_if:has_recovery,1|nullable|date',
            'recovery_end_date' => 'required_if:has_recovery,1|nullable|date|after_or_equal:recovery_start_date',
        ]);

        // Si no hay recuperatorio, asegurarse de que las fechas sean nulas
        if (!($validated['has_recovery'] ?? false)) {
            $validated['recovery_start_date'] = null;
            $validated['recovery_end_date'] = null;
        }

        // Actualizar el seguimiento
        $followup->update($validated);

        return redirect()->route('grade_followups.show', [$program->id, $module->id, $grade->id])
            ->with('success', 'Información de recuperatorio actualizada correctamente.');
    }

    /**
     * Cerrar un seguimiento.
     */
    public function close(Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        $followup = GradeFollowup::where('grade_id', $grade->id)
            ->where('status', 'open')
            ->firstOrFail();

        $followup->close();

        return redirect()->route('grade_followups.show', [$program->id, $module->id, $grade->id])
            ->with('success', 'Seguimiento cerrado correctamente.');
    }

    /**
     * Crear un nuevo seguimiento (cuando ya existe uno cerrado).
     */
    public function createNew(Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Verificar que no hay seguimientos abiertos
        $openFollowup = GradeFollowup::where('grade_id', $grade->id)
            ->where('status', 'open')
            ->first();

        if ($openFollowup) {
            return redirect()->route('grade_followups.show', [$program->id, $module->id, $grade->id])
                ->with('error', 'Ya existe un seguimiento abierto. Debe cerrar el seguimiento actual antes de crear uno nuevo.');
        }

        return view('grade_followups.create', compact('program', 'module', 'grade'));
    }

    /**
     * Mostrar el historial de seguimientos de una calificación.
     */
    public function history(Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        $followups = GradeFollowup::where('grade_id', $grade->id)
            ->with(['contacts', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('grade_followups.history', compact('program', 'module', 'grade', 'followups'));
    }

    /**
     * Mostrar un seguimiento específico del historial.
     */
    public function showFollowup(Program $program, Module $module, Grade $grade, GradeFollowup $followup)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Verificar que el seguimiento pertenece a esta calificación
        if ($followup->grade_id !== $grade->id) {
            abort(404);
        }

        return view('grade_followups.show_single', compact('program', 'module', 'grade', 'followup'));
    }
}
