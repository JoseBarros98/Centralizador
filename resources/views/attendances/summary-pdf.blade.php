<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Asistencia - {{ $module->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 16px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .header {
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            text-align: center;
            color: #666;
        }
        .present {
            background-color: #d1fae5;
            text-align: center;
        }
        .late {
            background-color: #fef3c7;
            text-align: center;
        }
        .absent {
            background-color: #fee2e2;
            text-align: center;
        }
        .attendance-bar {
            width: 100%;
            background-color: #e5e7eb;
            height: 10px;
            display: inline-block;
        }
        .attendance-progress {
            height: 10px;
            background-color: #3b82f6;
            display: inline-block;
        }
        .criteria {
            background-color: #f0f7ff;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Resumen de Asistencia</h1>
        <h2>Programa: {{ $program->name }}</h2>
        <h2>Módulo: {{ $module->name }}</h2>
        <p>Fecha de exportación: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="criteria">
        <p><strong>Criterios de asistencia:</strong></p>
        <ul>
            <li>Duración menor a 30 minutos: Se considera falta</li>
            <li>Entre 30 y 59 minutos: Asistencia parcial (tarde)</li>
            <li>60 minutos o más: Asistencia completa (presente)</li>
        </ul>
    </div>

    @if(count($classes) === 0)
        <div>
            <p>No hay clases registradas para este módulo.</p>
        </div>
    @elseif(count($inscriptions) === 0)
        <div>
            <p>No hay inscritos registrados para este programa.</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Inscrito</th>
                    <th>Documento</th>
                    @foreach($classes as $class)
                        <th class="text-center">
                            {{ $class->class_date->format('d/m') }}<br>
                            {{ $class->start_time->format('H:i') }}
                        </th>
                    @endforeach
                    <th class="text-center">Asistencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceMatrix as $row)
                    <tr>
                        <td>{{ $row['inscription']->getFullName() }}</td>
                        <td>{{ $row['inscription']->ci }}</td>
                        @foreach($classes as $class)
                            <td class="text-center">
                                @if(isset($row['classes'][$class->id]['status']))
                                    @if($row['classes'][$class->id]['status'] === 'present')
                                        <div class="present">P</div>
                                    @elseif($row['classes'][$class->id]['status'] === 'late')
                                        <div class="late">L</div>
                                    @elseif($row['classes'][$class->id]['status'] === 'absent')
                                        <div class="absent">F</div>
                                    @endif
                                @else
                                    <div class="absent">F</div>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center">
                            <div>
                                {{ $row['stats']['attended'] }}/{{ $row['stats']['total'] }}
                                ({{ round($row['stats']['percentage']) }}%)
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- <div class="footer">
        <p>Sistema de Gestión de Inscripciones - Este documento fue generado automáticamente.</p>
        <p>Página 1 de 1</p>
    </div> --}}
</body>
</html>
