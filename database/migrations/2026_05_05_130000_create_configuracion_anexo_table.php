<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_anexo', function (Blueprint $table) {
            $table->id();
            $table->string('empresa')->nullable();
            $table->string('ruc', 20)->nullable();
            $table->string('contrato', 100)->nullable();
            $table->string('division', 150)->nullable();
            $table->text('nombre_servicio_contratado')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_anexo');
    }
};
