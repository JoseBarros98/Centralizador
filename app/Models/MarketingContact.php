<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingContact extends Model
{
    protected $fillable = [
        'nombre', 'profesion', 'program_id', 'telefono',
        'referencia', 'origen', 'fecha', 'estado_pago',
        'conversion', 'inscription_id', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'fecha'      => 'date',
        'conversion' => 'boolean',
    ];

    const REFERENCIAS = [
        'No interesado',
        'Interesado',
        'Maestro',
        'Sin título en provisión nacional',
        'Sin respuesta',
    ];

    const ORIGENES = [
        'Facebook corporativo',
        'Facebook propio',
        'Base propia',
        'Base derivada',
    ];

    const ESTADOS_PAGO = [
        'Esperando pago',
        'Adelanto realizado',
        'Pago completo',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(MarketingContactResponse::class)->latest();
    }

    public function calls(): HasMany
    {
        return $this->hasMany(MarketingContactCall::class)->orderBy('fecha_llamada', 'desc');
    }
}
