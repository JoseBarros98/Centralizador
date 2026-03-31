<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'request_type',
        'payroll_number',
        'request_date',
        'invoice_number',
        'observations',
        'total_amount',
        'retention_amount',
        'net_amount',
        'total_active_students',
        'tutoring_teacher_id',
        'tutoring_start_date',
        'tutoring_end_date',
        'tutoring_students_count',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'request_date' => 'date',
        'tutoring_start_date' => 'date',
        'tutoring_end_date' => 'date',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function tutoringTeacher()
    {
        return $this->belongsTo(Teacher::class, 'tutoring_teacher_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Acceso a datos del programa a través del módulo
    public function getProgramAttribute()
    {
        return $this->module->program;
    }

    // Acceso al docente a través del módulo
    public function getTeacherAttribute()
    {
        // Si es tutoría y tiene docente asignado, usar ese, sino usar el del módulo
        if ($this->request_type === 'Tutoria' && $this->tutoringTeacher) {
            return $this->tutoringTeacher;
        }
        return $this->module->teacher;
    }

    // Obtener fecha de inicio según tipo de solicitud
    public function getStartDateAttribute()
    {
        if ($this->request_type === 'Tutoria' && $this->tutoring_start_date) {
            return $this->tutoring_start_date;
        }
        return $this->module->start_date;
    }

    // Obtener fecha de fin según tipo de solicitud
    public function getEndDateAttribute()
    {
        if ($this->request_type === 'Tutoria' && $this->tutoring_end_date) {
            return $this->tutoring_end_date;
        }
        return $this->module->finalization_date;
    }

    // Obtener cantidad de estudiantes según tipo de solicitud
    public function getStudentsCountAttribute()
    {
        if ($this->request_type === 'Tutoria' && $this->tutoring_students_count !== null) {
            return $this->tutoring_students_count;
        }
        return $this->total_active_students;
    }

    // Obtener código contable del programa
    public function getAccountingCodeAttribute()
    {
        return $this->module->program->accounting_code ?? 'N/A';
    }

    // Obtener nombre del programa
    public function getProgramNameAttribute()
    {
        return $this->module->program->name ?? 'N/A';
    }

    // Obtener área del programa
    public function getProgramAreaAttribute()
    {
        return $this->module->program->area ?? 'N/A';
    }

    // Obtener nombre del módulo
    public function getModuleNameAttribute()
    {
        return $this->module->name ?? 'N/A';
    }

    // Obtener nombre completo del docente
    public function getTeacherFullNameAttribute()
    {
        return $this->module->teacher->full_name ?? 'N/A';
    }

    // Obtener CI del docente
    public function getTeacherCiAttribute()
    {
        return $this->module->teacher->ci ?? 'N/A';
    }

    // Obtener si el docente emite factura
    public function getTeacherBillAttribute()
    {
        return $this->module->teacher->bill ?? 'No';
    }

    // Obtener banco del docente
    public function getTeacherBankAttribute()
    {
        return $this->module->teacher->bank ?? 'N/A';
    }

    // Obtener número de cuenta del docente
    public function getTeacherAccountNumberAttribute()
    {
        return $this->module->teacher->account_number ?? 'N/A';
    }

    // Calcular retención según el tipo de docente (escalonada)
    // - Si es trabajador ESAM y factura: retención del 30%
    // - Si es trabajador ESAM y NO factura: retención del 30% + 16% del saldo (41.2% total)
    // - Si NO es trabajador ESAM y factura: sin retención (0%)
    // - Si NO es trabajador ESAM y NO factura: retención del 16%
    public function calculateRetention($totalAmount, $teacherBills, $isEsamWorker)
    {
        $retentionAmount = 0;
        
        // Verificar si es trabajador ESAM
        if ($isEsamWorker === 'Si' || $isEsamWorker === 'Sí' || $isEsamWorker === 'si' || $isEsamWorker === 'sí') {
            // Primera retención: 30% por ser trabajador ESAM
            $retentionAmount = $totalAmount * 0.30;
            
            // Si además NO factura, aplicar 16% sobre el saldo (70%)
            if ($teacherBills !== 'Si' && $teacherBills !== 'Sí' && $teacherBills !== 'si' && $teacherBills !== 'sí') {
                $saldoDespuesEsam = $totalAmount - $retentionAmount; // 70% del total
                $retentionAmount += $saldoDespuesEsam * 0.16; // 16% del 70% = 11.2% adicional
                // Retención total: 30% + 11.2% = 41.2%
            }
        } else {
            // No es trabajador ESAM, solo verificar si factura
            if ($teacherBills !== 'Si' && $teacherBills !== 'Sí' && $teacherBills !== 'si' && $teacherBills !== 'sí') {
                $retentionAmount = $totalAmount * 0.16;
            }
        }
        
        return $retentionAmount;
    }

    // Calcular monto neto a pagar
    public function calculateNetAmount($totalAmount, $retentionAmount)
    {
        return $totalAmount - $retentionAmount;
    }
    
    // Obtener el tipo de retención aplicada
    public function getRetentionTypeAttribute()
    {
        if ($this->retention_amount == 0) {
            return 'Sin retención (Factura)';
        }
        
        // Determinar el docente
        $teacher = $this->request_type === 'Tutoria' && $this->tutoringTeacher 
            ? $this->tutoringTeacher 
            : $this->module->teacher;
        
        if ($teacher->esam_worker === 'Si' || $teacher->esam_worker === 'Sí') {
            // Verificar si factura
            if ($teacher->bill === 'Si' || $teacher->bill === 'Sí') {
                return 'Retención 30% (Trabajador ESAM)';
            } else {
                return 'Retención 41.2% (Trabajador ESAM + No factura)';
            }
        }
        
        return 'Retención 16% (No factura)';
    }
    
    // Obtener el porcentaje de retención aplicado
    public function getRetentionPercentageAttribute()
    {
        if ($this->retention_amount == 0) {
            return 0;
        }
        
        // Determinar el docente
        $teacher = $this->request_type === 'Tutoria' && $this->tutoringTeacher 
            ? $this->tutoringTeacher 
            : $this->module->teacher;
        
        if ($teacher->esam_worker === 'Si' || $teacher->esam_worker === 'Sí') {
            // Verificar si factura
            if ($teacher->bill === 'Si' || $teacher->bill === 'Sí') {
                return 30;
            } else {
                return 41.2; // 30% + 16% del 70%
            }
        }
        
        return 16;
    }
    
    // Obtener desglose de retención (para mostrar en vistas)
    public function getRetentionBreakdownAttribute()
    {
        if ($this->retention_amount == 0) {
            return ['total' => 0, 'esam' => 0, 'no_factura' => 0];
        }
        
        // Determinar el docente
        $teacher = $this->request_type === 'Tutoria' && $this->tutoringTeacher 
            ? $this->tutoringTeacher 
            : $this->module->teacher;
        
        $breakdown = ['total' => $this->retention_amount, 'esam' => 0, 'no_factura' => 0];
        
        if ($teacher->esam_worker === 'Si' || $teacher->esam_worker === 'Sí') {
            // Retención ESAM: 30%
            $breakdown['esam'] = $this->total_amount * 0.30;
            
            // Si no factura, agregar 16% del saldo
            if ($teacher->bill !== 'Si' && $teacher->bill !== 'Sí') {
                $saldo = $this->total_amount - $breakdown['esam'];
                $breakdown['no_factura'] = $saldo * 0.16;
            }
        } else {
            // No es trabajador ESAM, solo retención por no facturar
            if ($teacher->bill !== 'Si' && $teacher->bill !== 'Sí') {
                $breakdown['no_factura'] = $this->total_amount * 0.16;
            }
        }
        
        return $breakdown;
    }
}
