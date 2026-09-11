<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El TFG tenía una tabla `extras_coche` con veinte columnas booleanas —una por
 * extra— y añadir uno nuevo era una migración y tocar cuatro pantallas. Aquí los
 * extras son un catálogo y la relación es una tabla pivote: añadir «techo solar»
 * es insertar una fila.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('group', 20);
            $table->string('icon', 40)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
        });

        Schema::create('car_feature', function (Blueprint $table) {
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();

            $table->primary(['car_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_feature');
        Schema::dropIfExists('features');
    }
};
