<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El chat del TFG eran mensajes con `id_mensaje_entrante` e `id_mensaje_saliente`,
 * y para pintar una conversación había que pedir las dos direcciones y unirlas a
 * mano en cada carga. Aquí la conversación es una fila: dos personas, el coche del
 * que hablan, y sus mensajes colgando.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('renter_id')->constrained('users')->cascadeOnDelete();
            // Para ordenar la bandeja sin contar mensajes en cada carga.
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            // Una sola conversación por coche e interesado: si escribe otra vez,
            // continúa la que ya había.
            $table->unique(['car_id', 'renter_id']);
            $table->index(['owner_id', 'last_message_at']);
            $table->index(['renter_id', 'last_message_at']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
