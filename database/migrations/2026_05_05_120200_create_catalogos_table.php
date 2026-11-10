<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 100);
            $table->string('nombre', 150);
            $table->string('codigo', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tipo', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos');
    }
};
