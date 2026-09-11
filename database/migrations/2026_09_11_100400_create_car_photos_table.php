<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            // La primera foto es la de la portada del catálogo, así que el orden
            // importa y lo decide el dueño.
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['car_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_photos');
    }
};
