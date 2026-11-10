<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anexos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo')->nullable();
            $table->string('periodo_ejecucion_contractual')->nullable();
            $table->date('fecha_contractual_ingreso_entregable')->nullable();
            $table->string('responsable')->nullable();
            $table->string('estado', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anexos');
    }
};
