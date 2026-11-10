<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroAnexo extends Model
{
    use HasFactory;

    protected $table = 'registros_anexo';

    protected $fillable = [
        'anexo_id',
        'item',
        'codigo_unidad',
        'expediente_siged',
        'fecha_asignacion',
        'numero_documento',
        'codigo_osinergmin',
        'codigo_actividad',
        'razon_social_agente',
        'tipo_supervision',
        'tipo_entregable',
        'numero_informe',
        'visitado',
        'efectividad',
        'fecha_visita',
        'fecha_derivacion',
        'estado_entregable',
        'personal_es',
        'observaciones',
        'comentarios',
    ];

    protected function casts(): array
    {
        return [
            'fecha_asignacion' => 'date',
            'fecha_visita' => 'date',
            'fecha_derivacion' => 'date',
            'visitado' => 'boolean',
            'efectividad' => 'boolean',
        ];
    }

    public function anexo(): BelongsTo
    {
        return $this->belongsTo(Anexo::class);
    }
}
