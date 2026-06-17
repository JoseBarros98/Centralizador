<?php

namespace App\Http\Controllers;

use App\Models\MarketingProgram;
use App\Models\MarketingProgramWebinar;
use App\Models\MarketingTeam;
use App\Models\Program;
use App\Services\GoogleDriveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class MarketingProgramController extends Controller
{
    protected GoogleDriveService $drive;

    public function __construct(GoogleDriveService $drive)
    {
        $this->drive = $drive;
        $this->middleware('permission:marketing_program.view')->only(['index']);
        $this->middleware('permission:marketing_program.edit')->only([
            'store', 'update', 'destroy',
            'uploadTeacherPanel', 'deleteTeacherPanel',
            'storeWebinar', 'updateWebinar', 'destroyWebinar',
        ]);
    }

    public function index(Request $request): View
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        $programs = MarketingProgram::with(['marketingTeam', 'webinars', 'program'])
            ->where('gestion', $gestion)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => $this->formatProgram($p));

        $teams   = MarketingTeam::where('active', true)->orderBy('name')->get(['id', 'name']);
        $programs_list = Program::orderBy('name')->get(['id', 'name', 'area']);

        return view('marketing-programs.index', compact('programs', 'teams', 'programs_list', 'gestion'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateProgram($request);
        $validated['gestion'] = (int) $request->get('gestion', date('Y'));

        $program = MarketingProgram::create($validated);
        $program->load(['marketingTeam', 'webinars', 'program']);

        return response()->json(['success' => true, 'program' => $this->formatProgram($program)]);
    }

    public function update(Request $request, MarketingProgram $marketingProgram): JsonResponse
    {
        $validated = $this->validateProgram($request);
        $marketingProgram->update($validated);
        $marketingProgram->load(['marketingTeam', 'webinars', 'program']);

        return response()->json(['success' => true, 'program' => $this->formatProgram($marketingProgram)]);
    }

    public function destroy(MarketingProgram $marketingProgram): JsonResponse
    {
        if ($marketingProgram->teacher_panel_drive_id) {
            try { $this->drive->deleteFile($marketingProgram->teacher_panel_drive_id); } catch (\Exception) {}
        }
        foreach ($marketingProgram->webinars as $w) {
            if ($w->drive_file_id) {
                try { $this->drive->deleteFile($w->drive_file_id); } catch (\Exception) {}
            }
        }
        $marketingProgram->delete();

        return response()->json(['success' => true]);
    }

    // ── Plantel docente ──────────────────────────────────────────────────────

    public function uploadTeacherPanel(Request $request, MarketingProgram $marketingProgram): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:20480',
        ]);

        if ($marketingProgram->teacher_panel_drive_id) {
            try { $this->drive->deleteFile($marketingProgram->teacher_panel_drive_id); } catch (\Exception) {}
        }

        $file     = $request->file('file');
        $folder   = $this->drive->createHierarchicalFolder(
            'Marketing', 'Oferta Académica', (string) $marketingProgram->gestion,
            $marketingProgram->name, 'Plantel Docente'
        );
        $uploaded = $this->drive->uploadFile(
            $file->getRealPath(),
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            'Plantel docente - ' . $marketingProgram->name,
            $folder
        );

        $marketingProgram->update([
            'has_teacher_panel'       => true,
            'teacher_panel_drive_id'  => $uploaded['id'],
            'teacher_panel_drive_link'=> $uploaded['webViewLink'] ?? null,
        ]);

        return response()->json([
            'success'    => true,
            'drive_id'   => $uploaded['id'],
            'drive_link' => $uploaded['webViewLink'] ?? null,
        ]);
    }

    public function deleteTeacherPanel(MarketingProgram $marketingProgram): JsonResponse
    {
        if ($marketingProgram->teacher_panel_drive_id) {
            try { $this->drive->deleteFile($marketingProgram->teacher_panel_drive_id); } catch (\Exception) {}
        }

        $marketingProgram->update([
            'has_teacher_panel'        => false,
            'teacher_panel_drive_id'   => null,
            'teacher_panel_drive_link' => null,
        ]);

        return response()->json(['success' => true]);
    }

    // ── Webinars ─────────────────────────────────────────────────────────────

    public function storeWebinar(Request $request, MarketingProgram $marketingProgram): JsonResponse
    {
        $validated = $this->validateWebinar($request);
        $driveData = $this->handleWebinarFile($request, $marketingProgram, null);

        $webinar = $marketingProgram->webinars()->create(array_merge($validated, $driveData));

        return response()->json(['success' => true, 'webinar' => $this->formatWebinar($webinar)]);
    }

    public function updateWebinar(Request $request, MarketingProgramWebinar $webinar): JsonResponse
    {
        $validated = $this->validateWebinar($request);
        $driveData = $this->handleWebinarFile($request, $webinar->marketingProgram, $webinar);

        $webinar->update(array_merge($validated, $driveData));

        return response()->json(['success' => true, 'webinar' => $this->formatWebinar($webinar->fresh())]);
    }

    public function destroyWebinar(MarketingProgramWebinar $webinar): JsonResponse
    {
        if ($webinar->drive_file_id) {
            try { $this->drive->deleteFile($webinar->drive_file_id); } catch (\Exception) {}
        }
        $webinar->delete();

        return response()->json(['success' => true]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validateProgram(Request $request): array
    {
        return $request->validate([
            'status'               => 'required|string|in:en_oferta,en_desarrollo,en_conflicto,pendiente_lanzamiento,grupo_cerrado,sin_exito',
            'name'                 => 'required|string|max:255',
            'certification'        => 'nullable|string|max:255',
            'program_id'           => 'nullable|integer|exists:programs,id',
            'marketing_team_id'    => 'nullable|integer|exists:marketing_teams,id',
            'class_days'           => 'nullable|array',
            'class_days.*'         => 'string|in:lunes,martes,miércoles,jueves,viernes,sábado,domingo',
            'campaign_launch_date' => 'nullable|date',
            'inauguration_date'    => 'nullable|date',
            'module_0_date'        => 'nullable|date',
            'module_1_date'        => 'nullable|date',
        ]);
    }

    private function validateWebinar(Request $request): array
    {
        return $request->validate([
            'label' => 'nullable|string|max:100',
            'date'  => 'nullable|date',
            'link'  => 'nullable|url|max:500',
        ]);
    }

    private function handleWebinarFile(Request $request, MarketingProgram $program, ?MarketingProgramWebinar $existing): array
    {
        if (!$request->hasFile('file')) {
            return [];
        }

        if ($existing?->drive_file_id) {
            try { $this->drive->deleteFile($existing->drive_file_id); } catch (\Exception) {}
        }

        $file   = $request->file('file');
        $folder = $this->drive->createHierarchicalFolder(
            'Marketing', 'Oferta Académica', (string) $program->gestion,
            $program->name, 'Webinars'
        );
        $uploaded = $this->drive->uploadFile(
            $file->getRealPath(),
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            'Webinar - ' . $program->name,
            $folder
        );

        return [
            'drive_file_id'   => $uploaded['id'],
            'drive_file_link' => $uploaded['webViewLink'] ?? null,
        ];
    }

    private function formatProgram(MarketingProgram $p): array
    {
        // Totals from inscription_program pivot
        $totalInscritos    = 0;
        $inscritosCompletos = 0;
        if ($p->program_id) {
            $totalInscritos     = \DB::table('inscription_program')->where('program_id', $p->program_id)->count();
            $inscritosCompletos = \DB::table('inscription_program')
                ->join('inscriptions', 'inscriptions.id', '=', 'inscription_program.inscription_id')
                ->where('inscription_program.program_id', $p->program_id)
                ->where('inscriptions.academic_status', 'completo')
                ->count();
        }

        return [
            'id'                       => $p->id,
            'status'                   => $p->status,
            'name'                     => $p->name,
            'certification'            => $p->certification,
            'program_id'               => $p->program_id,
            'area'                     => $p->program?->area ?? '',
            'marketing_team_id'        => $p->marketing_team_id,
            'marketing_team_name'      => $p->marketingTeam?->name ?? '',
            'class_days'               => $p->class_days ?? [],
            'has_teacher_panel'        => $p->has_teacher_panel,
            'teacher_panel_drive_id'   => $p->teacher_panel_drive_id,
            'teacher_panel_drive_link' => $p->teacher_panel_drive_link,
            'campaign_launch_date'     => $p->campaign_launch_date?->format('Y-m-d'),
            'inauguration_date'        => $p->inauguration_date?->format('Y-m-d'),
            'module_0_date'            => $p->module_0_date?->format('Y-m-d'),
            'module_1_date'            => $p->module_1_date?->format('Y-m-d'),
            'gestion'                  => $p->gestion,
            'total_inscritos'          => $totalInscritos,
            'inscritos_completos'      => $inscritosCompletos,
            'webinars'                 => $p->webinars->map(fn($w) => $this->formatWebinar($w))->values(),
        ];
    }

    private function formatWebinar(MarketingProgramWebinar $w): array
    {
        return [
            'id'             => $w->id,
            'label'          => $w->label,
            'date'           => $w->date?->format('Y-m-d'),
            'link'           => $w->link,
            'drive_file_id'  => $w->drive_file_id,
            'drive_file_link'=> $w->drive_file_link,
        ];
    }
}
