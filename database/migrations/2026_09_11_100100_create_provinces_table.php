<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El TFG guardaba la provincia como texto libre en cada coche y luego la comparaba
 * con un `WHERE coche.provincia = ?`, así que «Málaga», «malaga» y «Malaga» eran
 * tres provincias distintas y el catálogo se quedaba vacío sin decir por qué.
 * Aquí la provincia es una fila con su código del INE.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->string('code', 2)->primary();
            $table->string('name');
            $table->string('region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
