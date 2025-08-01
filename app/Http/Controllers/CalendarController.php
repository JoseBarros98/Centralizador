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
        // Obtener todas las clases
        $classes = ModuleClass::with(['module', 'module.program'])->get();
        
        $events = [];
        
        foreach ($classes as $class) {
            $module = $class->module;
            $program = $module->program;
            
            // Crear un evento para cada clase
            $events[] = [
                'id' => $class->id,
                'title' => $module->name,
                'start' => $class->class_date->format('Y-m-d') . 'T' . $class->start_time->format('H:i:s'),
                'end' => $class->class_date->format('Y-m-d') . 'T' . $class->end_time->format('H:i:s'),
                'url' => route('programs.modules.classes.show', [$program->id, $module->id, $class->id]),
                'extendedProps' => [
                    'program' => $program->name,
                    'module' => $module->name,
                    'teacher' => $module->teacher_name,
                    'location' => $program->location,
                    'class_link' => $class->class_link,
                ]
            ];
        }
        
        return response()->json($events);
    }
}
