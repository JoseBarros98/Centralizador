<?php

namespace App\Models\External;

use Illuminate\Database\Eloquent\Model;

class ExternalProgram extends Model
{
    /**
     * Conexión a la base de datos externa
     */
    protected $connection = 'mysql_external';

    /**
     * Nombre de la vista en la base de datos externa
     */
    protected $table = 'vista_programas_latam';

    /**
     * La vista no tiene timestamps
     */
    public $timestamps = false;

    /**
     * No se puede insertar o actualizar en una vista
     */
    protected $guarded = ['*'];

    /**
     * Casts de los campos
     */
    protected $casts = [
        'id_programa' => 'string',
        'codigo_contable' => 'string',
        'nombre_programa' => 'string',
        'version_programa' => 'integer',
        'grupo_programa' => 'integer',
        'gestion_programa' => 'string',
        'fecha_matriculacion' => 'date',
        'fecha_inicio' => 'date',
        'fecha_finalizacion' => 'date',
        'modalidad' => 'string',
        'nota_de_aprobacion' => 'decimal:2',
        'fase_programa' => 'string',
        'cantidad_preinscritos' => 'integer',
        'cantidad_inscritos' => 'integer',
        'cantidad_retirados' => 'integer',
        'total_registros' => 'integer',
        'area_posgrado' => 'string',
    ];

    /**
     * Mapea los datos externos al formato del modelo local
     */
    public function toLocalFormat(): array
    {
        return [
            'code' => $this->id_programa,
            'accounting_code' => $this->codigo_contable,
            'name' => $this->nombre_programa,
            'version' => $this->version_programa ?? 1,
            'group' => $this->grupo_programa ?? 1,
            'year' => $this->gestion_programa,
            'start_date' => $this->fecha_inicio,
            'finalization_date' => $this->fecha_finalizacion,
            'status' => $this->fase_programa,
            // Nuevos campos estáticos
            'postgraduate_id' => $this->id_posgrado,
            'registration_date' => $this->fecha_matriculacion,
            'modality' => $this->modalidad,
            'passing_grade' => $this->nota_de_aprobacion,
        ];
    }
}
