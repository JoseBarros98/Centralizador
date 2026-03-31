<?php

namespace App\Http\Controllers;

use App\Models\ModuleClass;
use App\Models\Module;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:program.view');
    }

    public function index()
    {
        return view('calendar.index');
    }

    public function getEvents()
    {
        $user = Auth::user();
        $canViewAllModules = $user && method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['admin', 'academic', 'academico'])
            : false;

        $classes = ModuleClass::with([
            'module',
            'module.program',
            'module.teacher',
            'module.monitor',
        ])
            ->when(!$canViewAllModules && $user, function ($query) use ($user) {
                $query->whereHas('module', function ($moduleQuery) use ($user) {
                    $moduleQuery->where('monitor_id', $user->id);
                });
            })
            ->orderBy('class_date')
            ->orderBy('start_time')
            ->get();

        $seriesCounts = $classes
            ->pluck('google_calendar_event_id')
            ->filter(static fn ($id) => is_string($id) && $id !== '')
            ->countBy();

        $events = [];

        foreach ($classes as $class) {
            $module = $class->module;
            if (!$module) {
                continue;
            }

            $program = $module->program;

            if (!$program) {
                continue;
            }

            $calendarEventId = $class->google_calendar_event_id;
            $seriesSize = (int) ($calendarEventId && isset($seriesCounts[$calendarEventId])
                ? $seriesCounts[$calendarEventId]
                : 0);

            $events[] = [
                'id' => 'module-class-' . $class->id,
                'title' => $module->name,
                'start' => $class->class_date->format('Y-m-d') . 'T' . $class->start_time->format('H:i:s'),
                'end' => $class->class_date->format('Y-m-d') . 'T' . $class->end_time->format('H:i:s'),
                'url' => route('programs.modules.classes.show', [$program->id, $module->id, $class->id]),
                'extendedProps' => [
                    'class_id' => $class->id,
                    'program' => $program->name,
                    'module' => $module->name,
                    'teacher' => $module->teacher ? $module->teacher->full_name : 'No asignado',
                    'monitor' => $module->monitor ? $module->monitor->name : 'No asignado',
                    'location' => $program->location ?? '',
                    'class_link' => $class->module->shared_google_meet_link ?: ($class->google_meet_link ?: $class->class_link),
                    'google_calendar_event_id' => $calendarEventId,
                    'google_calendar_event_link' => $class->google_calendar_event_link,
                    'is_recurring_series' => $seriesSize > 1,
                    'recurring_series_size' => $seriesSize,
                    'google_synced_at' => optional($class->google_synced_at)?->toIso8601String(),
                    'google_sync_error' => $class->google_sync_error,
                ]
            ];
        }

        return response()->json($events);
    }
}
