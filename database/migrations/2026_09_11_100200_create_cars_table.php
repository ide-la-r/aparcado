<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

            $table->string('plate', 12)->unique();
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('registration_year');
            $table->unsignedInteger('kilometres');
            $table->string('fuel', 20);
            $table->string('transmission', 20);
            $table->string('body_type', 30);
            $table->string('colour', 30);
            $table->unsignedTinyInteger('seats');
            $table->unsignedTinyInteger('doors');
            $table->unsignedSmallInteger('power_hp');
            $table->boolean('has_insurance')->default(false);

            // Precio por día, en céntimos: el TFG lo guardaba como decimal y lo
            // paseaba por la URL hasta PayPal.
            $table->unsignedInteger('price_cents');
            $table->text('description')->nullable();

            $table->string('address');
            $table->string('city');
            $table->string('province_code', 2);
            $table->string('postal_code', 5);
            $table->string('country', 2)->default('ES');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('parking_type', 30)->nullable();

            // Un coche se puede esconder del catálogo sin borrarlo, que es lo que
            // de verdad quiere el dueño cuando lo necesita para él una temporada.
            $table->boolean('published')->default(true);

            $table->timestamps();
            // Borrado suave: una reserva pasada apunta a su coche, y el historial
            // no puede quedarse colgando.
            $table->softDeletes();

            $table->foreign('province_code')->references('code')->on('provinces');
            $table->index(['province_code', 'published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
