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
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->string('tipo'); // citas, ingresos, pacientes
            $table->json('filtros')->nullable();
            $table->string('formato'); // pdf, excel
            $table->string('archivo_path')->nullable();
            $table->string('estado')->default('generando'); // generando, listo, error
            $table->string('usuario_id', 50);
            $table->timestamp('fecha_generacion')->useCurrent();
            $table->timestamps();

            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};

