<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reemplaza la tabla 'users' de Laravel por 'roles' + 'usuarios'
     * para el sistema de atención domiciliaria.
     */
    public function up(): void
    {
        // ----------------------------------------------------------------
        // ROLES
        // ----------------------------------------------------------------
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id_rol');
            $table->string('nombre', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // ----------------------------------------------------------------
        // USUARIOS  (reemplaza la tabla 'users' de Laravel)
        // ----------------------------------------------------------------
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->unsignedInteger('id_rol');
            $table->string('nombre_completo', 150);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->tinyInteger('estado')->default(1)->comment('1=Activo, 0=Inactivo');
            $table->rememberToken();           // compatibilidad con Laravel Auth
            $table->timestamps();

            $table->foreign('id_rol')->references('id_rol')->on('roles');
        });

        // ----------------------------------------------------------------
        // LOGS DE ACCESO
        // ----------------------------------------------------------------
        Schema::create('logs_acceso', function (Blueprint $table) {
            $table->bigIncrements('id_log');
            $table->unsignedInteger('id_usuario');
            $table->string('accion', 50)->default('LOGIN');
            $table->string('ip_origen', 45)->nullable();
            $table->string('dispositivo', 255)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });

        // ----------------------------------------------------------------
        // TABLAS DE INFRAESTRUCTURA DE LARAVEL
        // ----------------------------------------------------------------
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id')->nullable()->index(); // apunta a id_usuario
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('logs_acceso');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};
