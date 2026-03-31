<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\External\ExternalProgram;
use App\Models\External\ExternalPostgraduate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'accounting_code',
        'name',
        'version',
        'group',
        'year',
        'start_date',
        'finalization_date',
        'moodle_link',
        'status',
        'postgraduate_id',
        'registration_date',
        'modality',
        'passing_grade',
        'academic_code',
        'area',
    ];

    protected $casts = [
        'start_date' => 'date',
        'finalization_date' => 'date',
        'registration_date' => 'date',
        'version' => 'integer',
        'group' => 'integer',
        'passing_grade' => 'decimal:2',
    ];

    /**
     * Relación muchos-a-muchos con inscripciones
     * Un programa puede tener múltiples inscripciones, y una inscripción puede estar en múltiples programas
     */
    public function inscriptions()
    {
        return $this->belongsToMany(Inscription::class, 'inscription_program')
                    ->withTimestamps();
    }

    /**
     * Relación con posgrado externo
     * Un programa pertenece a un posgrado
     */
    public function postgraduate()
    {
        return $this->belongsTo(ExternalPostgraduate::class, 'postgraduate_id', 'id_posgrado');
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    /**
     * Módulos seleccionados para mostrar en la vista del programa (relación muchos-a-muchos)
     */
    public function selectedModules()
    {
        return $this->belongsToMany(Module::class, 'program_module')
            ->withPivot('order')
            ->orderBy('program_module.order');
    }

    /**
     * Obtiene los datos dinámicos del programa desde la BD externa
     * (cantidad de inscritos, preinscritos, etc.)
     * Con caché de 5 minutos para evitar consultas excesivas
     */
    public function getExternalLiveData()
    {
        return Cache::remember("program_live_data_{$this->code}", 300, function () {
            try {
                $externalData = ExternalProgram::where('id_programa', $this->code)->first();
                
                if ($externalData) {
                    return [
                        'cantidad_preinscritos' => $externalData->cantidad_preinscritos ?? 0,
                        'cantidad_inscritos' => $externalData->cantidad_inscritos ?? 0,
                        'cantidad_retirados' => $externalData->cantidad_retirados ?? 0,
                        'total_registros' => $externalData->total_registros ?? 0,
                    ];
                }
                
                return null;
            } catch (\Exception $e) {
                Log::error("Error obteniendo datos en tiempo real del programa {$this->code}: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Accessor para obtener cantidad de preinscritos en tiempo real
     */
    public function getPreregisteredCountAttribute()
    {
        $liveData = $this->getExternalLiveData();
        return $liveData['cantidad_preinscritos'] ?? 0;
    }

    /**
     * Accessor para obtener cantidad de inscritos en tiempo real
     */
    public function getRegisteredCountAttribute()
    {
        $liveData = $this->getExternalLiveData();
        return $liveData['cantidad_inscritos'] ?? 0;
    }

    /**
     * Accessor para obtener cantidad de retirados en tiempo real
     */
    public function getWithdrawnCountAttribute()
    {
        $liveData = $this->getExternalLiveData();
        return $liveData['cantidad_retirados'] ?? 0;
    }

    /**
     * Accessor para obtener total de registros en tiempo real
     */
    public function getTotalRecordsAttribute()
    {
        $liveData = $this->getExternalLiveData();
        return $liveData['total_registros'] ?? 0;
    }

    /**
     * Obtiene el nombre de la modalidad legible
     */
    public function getModalityNameAttribute()
    {
        $modalities = [
            'V' => 'Virtual',
            'P' => 'Presencial',
            'S' => 'Semipresencial',
            'H' => 'Híbrido',
        ];

        return $modalities[$this->modality] ?? $this->modality;
    }
}
