<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_anexo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anexo_id')->constrained('anexos')->cascadeOnDelete();
            $table->unsignedInteger('item');
            $table->string('codigo_unidad', 100)->nullable();
            $table->string('expediente_siged', 100)->nullable();
            $table->date('fecha_asignacion')->nullable();
            $table->string('numero_documento', 100)->nullable();
            $table->string('codigo_osinergmin', 100)->nullable();
            $table->string('codigo_actividad', 100)->nullable();
            $table->string('razon_social_agente')->nullable();
            $table->string('tipo_supervision', 150)->nullable();
            $table->string('tipo_entregable', 150)->nullable();
            $table->string('numero_informe', 100)->nullable();
            $table->boolean('visitado')->default(false);
            $table->boolean('efectividad')->default(false);
            $table->date('fecha_visita')->nullable();
            $table->date('fecha_derivacion')->nullable();
            $table->string('estado_entregable', 100)->nullable();
            $table->string('personal_es', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->text('comentarios')->nullable();
            $table->timestamps();

            $table->unique(['anexo_id', 'item']);
            $table->index(['anexo_id', 'estado_entregable']);
            $table->index(['visitado', 'efectividad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_anexo');
    }
};
