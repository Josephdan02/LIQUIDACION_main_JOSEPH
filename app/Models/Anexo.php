<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Anexo extends Model
{
    use HasFactory;

    protected $table = 'anexos';

    protected $fillable = [
        'titulo',
        'periodo_ejecucion_contractual',
        'fecha_contractual_ingreso_entregable',
        'responsable',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_contractual_ingreso_entregable' => 'date',
        ];
    }

    public function registros(): HasMany
    {
        return $this->hasMany(RegistroAnexo::class);
    }

    public function getNombreArchivoExportacionAttribute(): string
    {
        $slug = Str::upper(Str::slug($this->titulo ?: 'ANEXO_'.$this->id, '_'));

        return "ANEXO_A_{$slug}.xlsx";
    }
}
