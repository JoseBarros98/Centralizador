<?php

namespace App\Exports;

use App\Models\PaymentRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Log;

class PaymentRequestsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $year;
    protected $month;
    protected $status;
    protected $requestType;
    protected $payrollNumber;
    protected $teacher;
    protected $program;
    protected $module;

    public function __construct($year = null, $month = null, $status = null, $requestType = null, $payrollNumber = null, $teacher = null, $program = null, $module = null)
    {
        $this->year = $year;
        $this->month = $month;
        $this->status = $status;
        $this->requestType = $requestType;
        $this->payrollNumber = $payrollNumber;
        $this->teacher = $teacher;
        $this->program = $program;
        $this->module = $module;
    }

    protected function getQuery()
    {
        $query = PaymentRequest::with(['module.program', 'module.teacher', 'tutoringTeacher']);

        // Obtener los años disponibles
        $currentYear = now()->format('Y');
        $currentMonth = now()->format('m');
        
        // Aplicar filtro de año
        if ($this->year) {
            $query->whereRaw("YEAR(request_date) = ?", [$this->year]);
        } else {
            $query->whereRaw("YEAR(request_date) = ?", [$currentYear]);
        }
        
        // Aplicar filtro de mes
        if ($this->month) {
            $month = str_pad($this->month, 2, '0', STR_PAD_LEFT);
            $query->whereRaw("MONTH(request_date) = ?", [$month]);
        } else {
            if (!$this->year || $this->year == $currentYear) {
                $query->whereRaw("MONTH(request_date) = ?", [$currentMonth]);
            }
        }

        // Búsqueda por número de planilla
        if ($this->payrollNumber) {
            $query->where('payroll_number', 'like', "%{$this->payrollNumber}%");
        }

        // Búsqueda por docente
        if ($this->teacher) {
            $search = $this->teacher;
            $query->where(function($q) use ($search) {
                $q->whereHas('module.teacher', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhereHas('tutoringTeacher', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        // Búsqueda por programa
        if ($this->program) {
            $query->whereHas('module.program', function($q) {
                $q->where('name', 'like', "%{$this->program}%");
            });
        }

        // Búsqueda por módulo
        if ($this->module) {
            $query->whereHas('module', function($q) {
                $q->where('name', 'like', "%{$this->module}%");
            });
        }

        // Filtro por estado
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // Filtro por tipo de solicitud
        if ($this->requestType) {
            $query->where('request_type', $this->requestType);
        }

        return $query;
    }

    public function collection()
    {
        return $this->getQuery()->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'N°',
            'Sede',
            'Mes',
            'N° Planilla',
            'Fecha Solicitud',
            'Cod. Contable',
            'Programa',
            'Módulo',
            'Fecha Inicio',
            'Fecha Fin',
            'Área',
            'N° de Estudiantes',
            'Docente',
            'Carnet',
            'Factura',
            'N° Factura',
            'Importe Total',
            'Base 70% (ESAM)',
            'RET ESAM',
            'Total Retención',
            'Líquido Pagable',
            'Banco',
            'N° Cuenta',
            'Observaciones',
        ];
    }

    public function map($row): array
    {
        static $count = 0;
        $count++;

        $breakdown = is_array($row->retention_breakdown) ? $row->retention_breakdown : json_decode($row->retention_breakdown, true) ?? [];
        $esam70 = ($row->total_amount ?? 0) * 0.70;

        // Obtener el área
        $area = 'No disponible';
        try {
            if ($row->module && $row->module->program && $row->module->program->postgraduate_id) {
                $postgrad = \App\Models\External\ExternalPostgraduate::where('id_posgrado', $row->module->program->postgraduate_id)->first();
                if ($postgrad && $postgrad->area_posgrado) {
                    $area = $postgrad->area_posgrado;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error obteniendo área: " . $e->getMessage());
        }

        // Obtener docente y datos
        $teacher = null;
        if ($row->request_type === 'Tutoria' && $row->tutoringTeacher) {
            $teacher = $row->tutoringTeacher;
        } else if ($row->module && $row->module->teacher) {
            $teacher = $row->module->teacher;
        }

        // Formatear fechas correctamente
        $requestDate = $this->parseDate($row->request_date);
        $startDate = $this->parseDate($row->module->start_date ?? null);
        $finalizationDate = $this->parseDate($row->module->finalization_date ?? null);

        $teacherName = $teacher ? ($teacher->full_name ?? ($teacher->first_name . ' ' . $teacher->last_name ?? '-')) : '-';
        $teacherCI = $teacher ? ($teacher->ci ?? '-') : '-';
        $teacherBank = $teacher ? ($teacher->bank ?? '-') : '-';
        $teacherAccount = $teacher ? ($teacher->account_number ?? '-') : '-';

        return [
            $count,
            'ESAM LATAM ALAS',
            $requestDate ? $requestDate->locale('es')->getTranslatedMonthName('long') : '-',
            $row->payroll_number ?? '-',
            $requestDate ? $requestDate->format('d/m/Y') : '-',
            $row->module->program->accounting_code ?? '-',
            $row->module->program->name ?? '-',
            $row->module->name ?? '-',
            $startDate ? $startDate->format('d/m/Y') : '-',
            $finalizationDate ? $finalizationDate->format('d/m/Y') : '-',
            $area,
            $row->total_active_students ?? 0,
            $teacherName,
            $teacherCI,
            ($row->net_amount * 0.845 > 0) ? 'Sí' : '-',
            $row->invoice_number ?? '-',
            number_format($row->total_amount ?? 0, 2, '.', ''),
            number_format($esam70, 2, '.', ''),
            number_format($breakdown['esam'] ?? 0, 2, '.', ''),
            number_format($row->retention_amount ?? 0, 2, '.', ''),
            number_format($row->net_amount ?? 0, 2, '.', ''),
            $teacherBank,
            $teacherAccount,
            $row->observations ?? '-',
        ];
    }

    private function parseDate($date)
    {
        if (!$date) {
            return null;
        }
        
        if ($date instanceof \Carbon\Carbon) {
            return $date;
        }
        
        try {
            return \Carbon\Carbon::parse($date);
        } catch (\Exception $e) {
            Log::error("Error parsing date: " . $e->getMessage());
            return null;
        }
    }
}
