<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->date('birthdate')->nullable();

            // Documento de identidad. El TFG guardaba la identificación como clave
            // primaria de la tabla; aquí es un dato más, único pero prescindible:
            // se puede registrar antes de subir los papeles.
            $table->string('document_type', 20)->nullable();
            $table->string('document_number', 30)->nullable()->unique();

            $table->string('avatar_path')->nullable();
            $table->string('document_photo_path')->nullable();
            $table->string('licence_photo_path')->nullable();

            // Verificado = alguien ha comprobado los papeles. Hasta entonces se puede
            // entrar y mirar, pero no alquilar ni publicar.
            $table->timestamp('verified_at')->nullable();
            $table->boolean('active')->default(true);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
