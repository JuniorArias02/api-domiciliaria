<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ordenes_servicios', function (Blueprint $table) {
            $table->integer('id_orden_servicio_anterior')
                ->nullable()
                ->after('id_orden');

            $table->foreign('id_orden_servicio_anterior')
                ->references('id_orden_servicio')
                ->on('ordenes_servicios')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_servicios', function (Blueprint $table) {
            $table->dropForeign(['id_orden_servicio_anterior']);
            $table->dropColumn('id_orden_servicio_anterior');
        });
    }
};
