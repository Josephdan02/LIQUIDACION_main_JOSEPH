<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_anexo', function (Blueprint $table) {
            if (! Schema::hasColumn('configuracion_anexo', 'periodo_ejecucion_contractual')) {
                $table->string('periodo_ejecucion_contractual')->nullable()->after('nombre_servicio_contratado');
            }

            if (! Schema::hasColumn('configuracion_anexo', 'fecha_contractual_ingreso_entregable')) {
                $table->string('fecha_contractual_ingreso_entregable', 255)->nullable()->after('periodo_ejecucion_contractual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_anexo', function (Blueprint $table) {
            if (Schema::hasColumn('configuracion_anexo', 'fecha_contractual_ingreso_entregable')) {
                $table->dropColumn('fecha_contractual_ingreso_entregable');
            }

            if (Schema::hasColumn('configuracion_anexo', 'periodo_ejecucion_contractual')) {
                $table->dropColumn('periodo_ejecucion_contractual');
            }
        });
    }
};
