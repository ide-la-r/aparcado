<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La suscripción es del dueño, no del coche, y lo que compra es sitio en el
 * catálogo: los coches de un Premium salen antes que los de un Plus, y los de un
 * Plus antes que los de quien no paga. Los planes y sus precios viven en
 * `config/aparcado.php`; aquí sólo se guarda cuál tiene contratado y hasta cuándo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan', 20);
            $table->unsignedInteger('price_cents');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
