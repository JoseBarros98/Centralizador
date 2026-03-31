<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleClass;
use App\Models\Program;
use App\Services\GoogleCalendarService;
use App\Services\GoogleMeetService;
use Carbon\Carbon;
use Google\Service\Calendar\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ModuleClassController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:program.view'])->only(['index', 'show']);
        $this->middleware(['permission:program.create'])->only(['create', 'store']);
        $this->middleware(['permission:program.edit'])->only(['edit', 'update']);
        $this->middleware(['permission:program.delete'])->only(['destroy']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program, Module $module, ModuleClass $class)
    {
        return view('module_classes.show', compact('program', 'module', 'class'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Program $program, Module $module)
    {
        return view('module_classes.create', compact('program', 'module'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Program $program, Module $module)
    {
        $validated = $request->validate([
            'schedule_type' => ['required', Rule::in(['single', 'recurring'])],
            'class_date' => 'nullable|required_if:schedule_type,single|date',
            'range_start_date' => 'nullable|required_if:schedule_type,recurring|date',
            'range_end_date' => 'nullable|required_if:schedule_type,recurring|date|after_or_equal:range_start_date',
            'weekdays' => 'nullable|required_if:schedule_type,recurring|array|min:1',
            'weekdays.*' => ['required_with:weekdays', 'integer', Rule::in([0, 1, 2, 3, 4, 5, 6])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'class_link' => 'nullable|url',
            'create_google_meet' => 'nullable|boolean',
            'co_organizers' => 'nullable|string',
        ]);

        $classDates = $this->resolveClassDates($validated);

        if (empty($classDates)) {
            return back()
                ->withErrors(['weekdays' => 'No se generaron clases para el rango y los dias seleccionados.'])
                ->withInput();
        }

        $dateRangeError = $this->validateDatesWithinModuleRange($module, $classDates);

        if ($dateRangeError) {
            return back()
                ->withErrors(['range_start_date' => $dateRangeError])
                ->withInput();
        }

        $existingDates = $module->classes()
            ->whereIn('class_date', $classDates)
            ->where('start_time', $validated['start_time'])
            ->where('end_time', $validated['end_time'])
            ->pluck('class_date')
            ->map(static fn ($classDate) => Carbon::parse($classDate)->toDateString())
            ->all();

        $datesToCreate = array_values(array_diff($classDates, $existingDates));

        if (empty($datesToCreate)) {
            return back()
                ->withErrors(['class_date' => 'Ya existen clases con ese horario en las fechas seleccionadas.'])
                ->withInput();
        }

        $createdClasses = [];

        foreach ($datesToCreate as $classDate) {
            $createdClasses[] = $module->classes()->create([
                'class_date' => $classDate,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'class_link' => $validated['class_link'] ?? null,
            ]);
        }

        $coOrganizers = $this->parseCoOrganizers($request->input('co_organizers'));

        $isRecurringRequest = ($validated['schedule_type'] ?? 'single') === 'recurring';
        $calendarRecurringError = null;

        if ($isRecurringRequest) {
            $calendarRecurringError = $this->syncRecurringCalendarSeries($module->fresh(), $coOrganizers);
        }

        if ($request->boolean('create_google_meet') || $module->shared_google_meet_link) {
            $googleSyncError = $this->syncSharedModuleMeet($module->fresh(), $createdClasses[0], $coOrganizers);

            if ($googleSyncError) {
                return redirect()->route('programs.modules.show', [$program->id, $module->id])
                    ->with('warning', $this->buildStoreMessage(count($createdClasses), count($existingDates), 'Se guardo la programacion, pero no se pudo sincronizar con Google Meet: ' . $googleSyncError));
            }
        }

        if (!$isRecurringRequest) {
            $freshModule = $module->fresh()->loadMissing(['program', 'teacher', 'monitor']);
            foreach ($createdClasses as $createdClass) {
                $this->syncCalendarEventForClass($freshModule, $createdClass->fresh(), $coOrganizers);
            }
        }

        if ($calendarRecurringError) {
            return redirect()->route('programs.modules.show', [$program->id, $module->id])
                ->with('warning', $this->buildStoreMessage(count($createdClasses), count($existingDates), 'Las clases se crearon, pero no se pudo sincronizar la serie recurrente de Calendar: ' . $calendarRecurringError));
        }

        return redirect()->route('programs.modules.show', [$program->id, $module->id])
            ->with('success', $this->buildStoreMessage(count($createdClasses), count($existingDates)));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Program $program, Module $module, ModuleClass $class)
    {
        return view('module_classes.edit', compact('program', 'module', 'class'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program, Module $module, ModuleClass $class)
    {
        $validated = $request->validate([
            'class_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'class_link' => 'nullable|url',
            'create_google_meet' => 'nullable|boolean',
            'co_organizers' => 'nullable|string',
        ]);

        $class->update([
            'class_date' => $validated['class_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'class_link' => $validated['class_link'] ?? null,
        ]);

        $coOrganizers = $this->parseCoOrganizers($request->input('co_organizers'));

        if ($request->boolean('create_google_meet') || $module->shared_google_meet_link) {
            $googleSyncError = $this->syncSharedModuleMeet($module->fresh(), $class->fresh(), $coOrganizers);

            if ($googleSyncError) {
                return redirect()->route('programs.modules.show', [$program->id, $module->id])
                    ->with('warning', 'Clase actualizada, pero no se pudo sincronizar con Google Meet: ' . $googleSyncError);
            }
        }

        $sameSeriesCount = $class->google_calendar_event_id
            ? $module->classes()->where('google_calendar_event_id', $class->google_calendar_event_id)->count()
            : 0;

        if ($sameSeriesCount > 1) {
            $calendarRecurringError = $this->syncRecurringCalendarSeries($module->fresh(), $coOrganizers);

            if ($calendarRecurringError) {
                return redirect()->route('programs.modules.show', [$program->id, $module->id])
                    ->with('warning', 'Clase actualizada, pero no se pudo sincronizar la serie recurrente de Calendar: ' . $calendarRecurringError);
            }
        } else {
            $freshModule = $module->fresh()->loadMissing(['program', 'teacher', 'monitor']);
            $this->syncCalendarEventForClass($freshModule, $class->fresh(), $coOrganizers);
        }

        return redirect()->route('programs.modules.show', [$program->id, $module->id])
            ->with('success', 'Clase actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program, Module $module, ModuleClass $class)
    {
        $calendarEventId = $class->google_calendar_event_id;
        $sameSeriesCount = $calendarEventId
            ? $module->classes()->where('google_calendar_event_id', $calendarEventId)->count()
            : 0;

        $class->delete();

        if ($sameSeriesCount > 1) {
            $coOrganizers = is_array($module->shared_google_meet_co_organizers)
                ? $module->shared_google_meet_co_organizers
                : [];

            $calendarRecurringError = $this->syncRecurringCalendarSeries($module->fresh(), $coOrganizers);

            if ($calendarRecurringError) {
                return redirect()->route('programs.modules.show', [$program->id, $module->id])
                    ->with('warning', 'Clase eliminada, pero no se pudo re-sincronizar la serie recurrente de Calendar: ' . $calendarRecurringError);
            }
        }

        return redirect()->route('programs.modules.show', [$program->id, $module->id])
            ->with('success', 'Clase eliminada correctamente.');
    }

    /**
     * @param array<int, string> $coOrganizers
     */
    private function syncSharedModuleMeet(Module $module, ModuleClass $class, array $coOrganizers): ?string
    {
        try {
            $loadedModule = $module->loadMissing(['program', 'teacher', 'monitor', 'classes']);
            $existingMeetLink = $loadedModule->shared_google_meet_link ?: $this->findExistingModuleMeetLink($loadedModule);
            $meetDisplayName = trim($loadedModule->name . (($loadedModule->program?->name) ? ' - ' . $loadedModule->program->name : ''));
            $meetConfiguration = app(GoogleMeetService::class)->ensureSharedMeeting($existingMeetLink, $coOrganizers, $meetDisplayName);

            if (!$meetConfiguration['success']) {
                $message = implode(' ', $meetConfiguration['errors']);

                $loadedModule->update([
                    'shared_google_meet_sync_error' => $message,
                ]);

                Log::warning('No se pudo sincronizar el Meet compartido del modulo.', [
                    'module_class_id' => $class->id,
                    'module_id' => $loadedModule->id,
                    'meet_link' => $existingMeetLink,
                    'co_organizers' => $coOrganizers,
                    'meet_details' => $meetConfiguration['details'],
                    'meet_errors' => $meetConfiguration['errors'],
                ]);

                return $message;
            }

            $sharedMeetLink = $meetConfiguration['meet_link'];
            $meetingCode = $meetConfiguration['meeting_code'];

            $loadedModule->update([
                'shared_google_meet_link' => $sharedMeetLink,
                'shared_google_meet_space_name' => $meetConfiguration['space_name'],
                'shared_google_meet_meeting_code' => $meetingCode,
                'shared_google_meet_co_organizers' => $coOrganizers,
                'shared_google_meet_synced_at' => Carbon::now(),
                'shared_google_meet_sync_error' => null,
            ]);

            foreach ($loadedModule->classes as $moduleClass) {
                $moduleClass->update([
                    'class_link' => $sharedMeetLink,
                    'google_meet_link' => $sharedMeetLink,
                    'google_meet_conference_id' => $meetingCode,
                    'google_meet_co_organizers' => $coOrganizers,
                    'google_synced_at' => Carbon::now(),
                    'google_sync_error' => null,
                ]);
            }

            Log::info('Google Meet compartido configurado correctamente para el modulo.', [
                'module_class_id' => $class->id,
                'module_id' => $loadedModule->id,
                'meet_link' => $sharedMeetLink,
                'co_organizers' => $coOrganizers,
                'space_name' => $meetConfiguration['space_name'],
            ]);

            return null;
        } catch (\Throwable $exception) {
            Log::error('Error sincronizando clase con Google Meet.', [
                'module_class_id' => $class->id,
                'error' => $exception->getMessage(),
            ]);

            $class->update([
                'google_sync_error' => $exception->getMessage(),
            ]);

            return $exception->getMessage();
        }
    }

    /**
     * @param array<int, string> $coOrganizers
     */
    private function syncCalendarEventForClass(Module $module, ModuleClass $class, array $coOrganizers): void
    {
        if (!config('services.google_calendar.access_token')) {
            return;
        }

        try {
            $calendarService = app(GoogleCalendarService::class);
            $event = $calendarService->upsertClassEvent($module, $class, $coOrganizers);

            $meetLink = $event->getHangoutLink();
            $conferenceId = $this->extractConferenceId($event);

            $class->update([
                'google_calendar_event_id' => $event->getId(),
                'google_calendar_event_link' => $event->getHtmlLink(),
                'class_link' => $meetLink ?: $class->class_link,
                'google_meet_link' => $meetLink ?: $class->google_meet_link,
                'google_meet_conference_id' => $conferenceId ?: $class->google_meet_conference_id,
            ]);

            if ($meetLink && !$module->shared_google_meet_link) {
                $module->update([
                    'shared_google_meet_link' => $meetLink,
                    'shared_google_meet_meeting_code' => $conferenceId ?: $module->shared_google_meet_meeting_code,
                ]);
            }

            Log::info('Evento de Google Calendar sincronizado.', [
                'module_class_id' => $class->id,
                'event_id' => $event->getId(),
                'meet_link' => $meetLink,
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo sincronizar el evento de Calendar para la clase.', [
                'module_class_id' => $class->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array<int, string> $coOrganizers
     */
    private function syncRecurringCalendarSeries(Module $module, array $coOrganizers): ?string
    {
        if (!config('services.google_calendar.access_token')) {
            return null;
        }

        try {
            $loadedModule = $module->loadMissing(['program', 'teacher', 'monitor', 'classes']);
            $classes = $loadedModule->classes->sortBy('class_date')->values();

            if ($classes->isEmpty()) {
                return null;
            }

            $existingRecurringEventId = $classes
                ->pluck('google_calendar_event_id')
                ->filter(static fn ($id) => is_string($id) && $id !== '')
                ->first();

            $event = app(GoogleCalendarService::class)->upsertRecurringModuleEvent(
                $loadedModule,
                $classes->all(),
                is_string($existingRecurringEventId) ? $existingRecurringEventId : null,
                $coOrganizers
            );

            $meetLink = $event->getHangoutLink();
            $conferenceId = $this->extractConferenceId($event);

            foreach ($classes as $moduleClass) {
                $moduleClass->update([
                    'google_calendar_event_id' => $event->getId(),
                    'google_calendar_event_link' => $event->getHtmlLink(),
                    'class_link' => $meetLink ?: $moduleClass->class_link,
                    'google_meet_link' => $meetLink ?: $moduleClass->google_meet_link,
                    'google_meet_conference_id' => $conferenceId ?: $moduleClass->google_meet_conference_id,
                ]);
            }

            if ($meetLink) {
                $loadedModule->update([
                    'shared_google_meet_link' => $meetLink,
                    'shared_google_meet_meeting_code' => $conferenceId ?: $loadedModule->shared_google_meet_meeting_code,
                ]);
            }

            Log::info('Serie recurrente de Google Calendar sincronizada.', [
                'module_id' => $loadedModule->id,
                'event_id' => $event->getId(),
                'classes_count' => $classes->count(),
                'meet_link' => $meetLink,
            ]);

            return null;
        } catch (\Throwable $exception) {
            Log::warning('No se pudo sincronizar la serie recurrente de Calendar.', [
                'module_id' => $module->id,
                'error' => $exception->getMessage(),
            ]);

            return $exception->getMessage();
        }
    }

    private function extractConferenceId(Event $event): ?string
    {
        $conferenceData = $event->getConferenceData();

        if (!$conferenceData) {
            return null;
        }

        $conferenceId = $conferenceData->getConferenceId();

        return is_string($conferenceId) && $conferenceId !== ''
            ? strtolower($conferenceId)
            : null;
    }

    private function findExistingModuleMeetLink(Module $module): ?string
    {
        foreach ($module->classes as $existingClass) {
            $candidate = $existingClass->google_meet_link ?: $existingClass->class_link;

            if (is_string($candidate) && str_contains($candidate, 'meet.google.com/')) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function parseCoOrganizers(?string $coOrganizersRaw): array
    {
        if (!$coOrganizersRaw) {
            return [];
        }

        return collect(preg_split('/[,;\n]+/', $coOrganizersRaw) ?: [])
            ->map(static fn (string $email) => trim(strtolower($email)))
            ->filter(static fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<int, string>
     */
    private function resolveClassDates(array $validated): array
    {
        if (($validated['schedule_type'] ?? 'single') === 'single') {
            return [Carbon::parse($validated['class_date'])->toDateString()];
        }

        $startDate = Carbon::parse($validated['range_start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['range_end_date'])->startOfDay();
        $weekdays = collect($validated['weekdays'] ?? [])
            ->map(static fn ($weekday) => (int) $weekday)
            ->unique()
            ->values()
            ->all();

        $dates = [];
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            if (in_array($cursor->dayOfWeek, $weekdays, true)) {
                $dates[] = $cursor->toDateString();
            }

            $cursor->addDay();
        }

        return $dates;
    }

    /**
     * @param array<int, string> $classDates
     */
    private function validateDatesWithinModuleRange(Module $module, array $classDates): ?string
    {
        $firstClassDate = Carbon::parse($classDates[0])->startOfDay();
        $lastClassDate = Carbon::parse($classDates[count($classDates) - 1])->startOfDay();

        if ($module->start_date && $firstClassDate->lt($module->start_date->copy()->startOfDay())) {
            return 'La programacion no puede iniciar antes de la fecha de inicio del modulo.';
        }

        if ($module->finalization_date && $lastClassDate->gt($module->finalization_date->copy()->startOfDay())) {
            return 'La programacion no puede terminar despues de la fecha de finalizacion del modulo.';
        }

        return null;
    }

    private function buildStoreMessage(int $createdCount, int $skippedCount, ?string $suffix = null): string
    {
        $baseMessage = $createdCount === 1
            ? 'Se creo 1 clase correctamente.'
            : sprintf('Se crearon %d clases correctamente.', $createdCount);

        if ($skippedCount > 0) {
            $baseMessage .= ' ' . sprintf('Se omitieron %d fechas porque ya existian con el mismo horario.', $skippedCount);
        }

        if ($suffix) {
            $baseMessage .= ' ' . $suffix;
        }

        return $baseMessage;
    }
}
