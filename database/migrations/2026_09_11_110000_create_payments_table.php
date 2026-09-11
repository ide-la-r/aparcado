<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El rastro de cada cobro.
 *
 * En el TFG no existía: el pago se resolvía entero en el navegador
 * (`actions.order.capture()` y un aviso bonito) y en la base de datos no quedaba
 * ni una fila. Nadie podía decir quién había pagado qué, ni cuánto, ni cuándo.
 *
 * `payable` es una relación polimórfica porque lo que se paga hoy son reservas y
 * mañana serán también suscripciones, y eso no debería costar otra tabla.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('payable');

            $table->string('provider', 20)->default('paypal');
            // El identificador que da la pasarela. Único: es lo que impide cobrar
            // dos veces la misma orden si el navegador manda la vuelta dos veces.
            $table->string('provider_order_id')->unique();

            // El importe que se pidió, calculado aquí y nunca recibido del
            // navegador. Al capturar se compara con lo que dice la pasarela.
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('EUR');

            $table->string('status', 20)->default('created');
            $table->timestamp('captured_at')->nullable();

            // La respuesta de la pasarela tal cual, para poder mirar qué pasó
            // cuando algo no cuadre.
            $table->json('payload')->nullable();

            $table->timestamps();

            $table->index(['payable_type', 'payable_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
