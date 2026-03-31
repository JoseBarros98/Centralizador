<?php

namespace App\Models\External;

use Illuminate\Database\Eloquent\Model;

class ExternalPostgraduate extends Model
{
    protected $connection = 'mysql_external';
    protected $table = 'vista_posgrados_latam';
    protected $primaryKey = 'id_posgrado';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id_posgrado',
        'nombre_posgrado',
        'area_posgrado',
        'categoria_posgrado',
        'creditaje',
        'carga_horaria',
        'duracion',
        'unidad_duracion',
        'estado_posgrado',
    ];
}
