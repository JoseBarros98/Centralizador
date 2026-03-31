<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\NameMatcher;

class Inscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        
        // Campos sincronizados con DB externa
        'full_name',
        'ci',
        'birth_date',
        'phone',
        'email',
        'profession_id',
        'inscription_date',
        'payment_plan',
        'external_inscription_status',
        'external_academic_status',
        'external_degree_status',
        'external_university_enrolled',
        'external_preregistration_date',
        'external_advisor_id',
        'external_advisor_name',
        'external_program_id',
        'external_id',
        
        // Campos del sistema local
        'civil_status',
        'university_id',
        'program_id',
        'payment_method',
        'enrollment_fee',
        'first_installment',
        'total_paid',
        'status',
        'participant_status',
        'participant_justification',
        'local_payment_status', // Estado de pago local (independiente del estado académico externo)
        'payment_group_id', // ID de grupo para inscripciones multi-mes
        'consolidated_status', // Estado consolidado para reportes anuales
        'gender',
        'residence',
        'location',
        'notes',
        'certification',
        'created_by',
        'updated_by',

        //Documentación
        'has_identity_card',
        'has_degree_title',
        'has_academic_diploma',
        'has_birth_certificate',
        'has_commitment_letter',
        'commitment_letter_path',
        'document_observations',

        //Requisitos de Titulación
        'has_legalized_degree_title',
        'has_legalized_academic_diploma',
        'has_identity_card_graduation',
        'has_birth_certificate_original',
        'has_photos',
        'graduation_procedure_type',

        //Trabajo Final / Monografía
        'has_monograph_elaboration',
        'has_monograph_received',

        // Maestría - Fase de trabajo de grado
        'has_degree_work_presentation',
        'has_tutor_approval_report',
        'has_pre_defense',
        'has_defense',
        'has_defense_accounting_status',

        //Estado de Titulación
        'has_graduation_procedure',
        'has_graduation_received',
        'has_documents_delivered',
        'has_diplomas_delivered',

        //Contable Interno
        'internal_accounting_billing_status',
        'internal_accounting_amount_due',
        'internal_accounting_graduation_payment',

        //Contable Externo
        'external_accounting_registration',
        'external_accounting_enrollment',
        'external_accounting_tuition',
        'external_accounting_degrees',

        //Accesos
        'was_added_to_the_group',
        'accesses_were_sent',
        'mail_was_sent',

        //Estado académico
        'academic_status',
        'has_freezing_letter',
        'freezing_letter_path',
        'freezing_letter_observations',
        
        // Control de sincronización
        'is_synced',
        'last_synced_at'
    ];

    protected $casts = [
        'inscription_date' => 'date',
        'birth_date' => 'date',
        'external_preregistration_date' => 'date',
        'has_identity_card' => 'boolean',
        'has_degree_title' => 'boolean',
        'has_academic_diploma' => 'boolean',
        'has_birth_certificate' => 'boolean',
        'has_commitment_letter' => 'boolean',
        'has_legalized_degree_title' => 'boolean',
        'has_legalized_academic_diploma' => 'boolean',
        'has_identity_card_graduation' => 'boolean',
        'has_birth_certificate_original' => 'boolean',
        'has_photos' => 'boolean',
        'has_monograph_elaboration' => 'boolean',
        'has_monograph_received' => 'boolean',
        'has_graduation_procedure' => 'boolean',
        'has_graduation_received' => 'boolean',
        'has_documents_delivered' => 'boolean',
        'has_diplomas_delivered' => 'boolean',
        'was_added_to_the_group' => 'boolean',
        'accesses_were_sent' => 'boolean',
        'mail_was_sent' => 'boolean',
        'external_university_enrolled' => 'boolean',
        'is_synced' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Relación muchos-a-muchos con programas
     * Una inscripción puede pertenecer a múltiples programas
     */
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'inscription_program')
                    ->withTimestamps();
    }

    /**
     * Accessor de compatibilidad para obtener el primer programa
     * Retorna el primer programa asociado como modelo (no como relación)
     */
    public function getProgramAttribute()
    {
        // Si ya están cargados los programas, usar esa colección
        if ($this->relationLoaded('programs')) {
            return $this->programs->first();
        }
        // Si no, hacer la consulta
        return $this->programs()->first();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function documentFollowups()
    {
        return $this->hasMany(DocumentFollowup::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public static function normalizeName($name)
    {
        return NameMatcher::normalizeName($name);
    }

    public function externalAdvisor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFullName()
    {
        $fullName = $this->full_name;
        
        if (!$fullName) {
            return '';
        }
        
        // 1. Agregar espacio antes de cada letra mayúscula que le precede una letra minúscula o número
        // Esto maneja casos como "TeresaIsabelRodriguez" -> "Teresa Isabel Rodriguez"
        $withSpaces = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $fullName);
        
        // 2. Normalizar a mayúsculas (todas las letras en mayúscula)
        $normalized = mb_strtoupper($withSpaces, 'UTF-8');
        
        return $normalized;
    }

    /**
     * Obtener el nombre del asesor (del sistema o externo)
     */
    public function getAdvisorName()
    {
        // Si tiene nombre de asesor externo, usar ese
        if ($this->external_advisor_name) {
            return $this->external_advisor_name;
        }
        
        // Si no, usar el nombre del usuario creador
        return $this->creator ? $this->creator->name : 'N/A';
    }

    /**
     * Alias de compatibilidad para el campo de estado academico externo.
     * Si existe `estado_academico`, se prioriza; caso contrario usa
     * `external_academic_status`.
     */
    public function getEstadoAcademicoAttribute()
    {
        $estadoAcademico = $this->attributes['estado_academico'] ?? null;

        if (!is_null($estadoAcademico) && $estadoAcademico !== '') {
            return $estadoAcademico;
        }

        return $this->attributes['external_academic_status'] ?? null;
    }

    // Generar código único basado en nombre y CI
    public static function generateCode($fullName, $ci)
    {
        // Remover acentos y caracteres especiales
        $fullName = self::removeAccents($fullName);
        
        $nameInitials = strtoupper(substr($fullName, 0, 2));
        $ciLastFour = substr($ci, -4);
        $randomChars = strtoupper(substr(md5(uniqid()), 0, 3));
        
        return $nameInitials . $ciLastFour . $randomChars;
    }

    /**
     * Remover acentos y caracteres especiales de una cadena
     */
    private static function removeAccents($string)
    {
        $unwanted_array = [
            'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ä'=>'A', 'á'=>'a', 'à'=>'a', 'â'=>'a', 'ä'=>'a',
            'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e',
            'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i',
            'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Ö'=>'O', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'ö'=>'o',
            'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u',
            'Ñ'=>'N', 'ñ'=>'n',
        ];
        
        return strtr($string, $unwanted_array);
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id');
    }
    
    public function profession()
    {
        return $this->belongsTo(Profession::class, 'profession_id');
    }

    public function paymentHistory()
    {
        return $this->hasMany(InscriptionPaymentHistory::class, 'inscription_id');
    }

    /**
     * Obtener el estado de pago de esta inscripción para un mes específico
     * 
     * Busca en el historial el último estado registrado en ese mes
     * Intenta usar los datos ya cargados en memoria primero
     */
    public function getPaymentStatusForMonth($year, $month)
    {
        // Si la relación ya está cargada en memoria, usar esos datos
        if ($this->relationLoaded('paymentHistory')) {
            $history = $this->paymentHistory
                ->filter(function ($h) use ($year, $month) {
                    return $h->status_date->year == $year && $h->status_date->month == $month;
                })
                ->sortByDesc('status_date')
                ->first();
            
            if ($history) {
                return $history->new_status;
            }
        } else {
            // Si no está cargada, hacer la query
            $history = $this->paymentHistory()
                ->whereYear('status_date', $year)
                ->whereMonth('status_date', $month)
                ->orderBy('status_date', 'DESC')
                ->first();
            
            if ($history) {
                return $history->new_status;
            }
        }
        
        return null;
    }

    /**
     * Obtener todos los meses en los que esta inscripción tiene movimientos
     */
    public function getActiveMonths()
    {
        return $this->paymentHistory()
            ->selectRaw('YEAR(status_date) as year, MONTH(status_date) as month')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC')
            ->get();
    }

    /**
     * Accessor para obtener el estado de pago del mes actual (si está seteado)
     * Se usa en las vistas cuando se filtra por mes
     */
    public function getDisplayPaymentStatusAttribute()
    {
        // Si está seteado display_month_year, calcular estado para ese mes
        if (isset($this->display_month) && isset($this->display_year)) {
            $status = $this->getPaymentStatusForMonth($this->display_year, $this->display_month);
            if ($status) {
                return $status;
            }
        }
        
        // Si no, retornar local_payment_status o status
        return $this->local_payment_status ?? $this->status;
    }

    /**
     * Accessor para obtener el total pagado SOLO del mes actual (si está seteado)
     * Se usa en las vistas cuando se filtra por mes para mostrar el monto correcto
     */
    public function getDisplayTotalPaidAttribute()
    {
        // Si está seteado display_month_year, calcular total pagado para ese mes
        if (isset($this->display_month) && isset($this->display_year)) {
            $monthStart = \Carbon\Carbon::createFromDate($this->display_year, $this->display_month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            // Obtener el último cambio de estado EN ESTE MES
            $lastChangeThisMonth = $this->paymentHistory()
                ->whereBetween('status_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->orderBy('status_date', 'DESC')
                ->first();
            
            if ($lastChangeThisMonth) {
                return $lastChangeThisMonth->amount_paid;
            }
            
            // Si no hay cambios en este mes, buscar si la inscripción existe en este mes
            // y retornar 0 porque no hay pagos registrados
            return 0;
        }
        
        // Si no está seteado display_month, retornar el total_paid global
        return $this->attributes['total_paid'] ?? 0;
    }
}
