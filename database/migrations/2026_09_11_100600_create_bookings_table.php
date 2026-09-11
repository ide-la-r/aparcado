<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('renter_id')->constrained('users')->cascadeOnDelete();

            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedSmallInteger('days');

            // El precio se congela al reservar: si el dueño lo sube mañana, la
            // reserva de hoy sigue costando lo que costaba.
            $table->unsignedInteger('price_cents_per_day');
            $table->unsignedInteger('total_cents');

            $table->string('status', 20)->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // El índice que de verdad se usa: buscar si un coche está pillado en
            // un rango de fechas.
            $table->index(['car_id', 'starts_on', 'ends_on']);
            $table->index(['renter_id', 'starts_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
