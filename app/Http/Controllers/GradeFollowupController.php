<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeFollowup;
use App\Models\FollowupContact;
use App\Models\Module;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GradeFollowupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:program.view'])->only(['show']);
        $this->middleware(['permission:program.create'])->only(['create', 'store', 'edit', 'update', 'addContact', 'storeContact', 'addRecovery', 'storeRecovery']);
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
            return redirect()->route('grades.summary', [$program->id, $module->id])
                ->with('error', 'Solo se puede hacer seguimiento a calificaciones no aprobatorias.');
        }

        // Obtener o inicializar el seguimiento
        $followup = $grade->followup ?? new GradeFollowup();

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

        // Crear o actualizar el seguimiento
        $followup = GradeFollowup::updateOrCreate(
            ['grade_id' => $grade->id],
            $validated
        );

        return redirect()->route('grade_followups.show', [$program->id, $module->id, $grade->id])
            ->with('success', 'Seguimiento guardado correctamente.');
    }

    /**
     * Mostrar el seguimiento.
     */
    public function show(Program $program, Module $module, Grade $grade)
    {
        // Verificar que la calificación pertenezca al módulo
        if ($grade->module_id != $module->id) {
            abort(404);
        }

        // Obtener el seguimiento
        $followup = $grade->followup;

        if (!$followup) {
            return redirect()->route('grade_followups.create', [$program->id, $module->id, $grade->id]);
        }

        // Obtener los contactos ordenados por fecha
        $calls = $followup->calls()->orderBy('contact_date', 'desc')->get();
        $messages = $followup->messages()->orderBy('contact_date', 'desc')->get();

        return view('grade_followups.show', compact('program', 'module', 'grade', 'followup', 'calls', 'messages'));
    }

    /**
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
        $followup = $grade->followup ?? GradeFollowup::create(['grade_id' => $grade->id]);

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

        // Obtener el seguimiento
        $followup = $grade->followup;

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
        $followup = $grade->followup ?? GradeFollowup::create(['grade_id' => $grade->id]);

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

        // Obtener el seguimiento
        $followup = $grade->followup;

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
}
