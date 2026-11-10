<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionAnexo extends Model
{
    use HasFactory;

    protected $table = 'configuracion_anexo';

    protected $fillable = [
        'empresa',
        'ruc',
        'contrato',
        'division',
        'nombre_servicio_contratado',
        'periodo_ejecucion_contractual',
        'fecha_contractual_ingreso_entregable',
    ];
}
