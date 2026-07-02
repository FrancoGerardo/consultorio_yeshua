<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_pagos', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('servicio_id', 50);
            $table->integer('porcentaje_anticipo_minimo')->default(50)->comment('% mínimo para confirmar ficha');
            $table->boolean('permite_pago_total')->default(true)->comment('Permitir pagar 100% de una vez');
            $table->decimal('descuento_pago_total', 5, 2)->default(5.00)->comment('% descuento si paga todo');
            $table->boolean('permite_plan_cuotas')->default(false)->comment('Permitir pago en cuotas');
            $table->decimal('monto_minimo_cuotas', 10, 2)->nullable()->comment('Monto mínimo para acceder a cuotas');
            $table->integer('porcentaje_anticipo_cuotas')->default(30)->comment('% anticipo si elige cuotas');
            $table->integer('max_cuotas')->default(12)->comment('Número máximo de cuotas');
            $table->integer('intervalo_dias_cuota')->default(30)->comment('Días entre cada cuota');
            $table->timestamps();

            $table->foreign('servicio_id')->references('id')->on('servicios')->onDelete('cascade');
            $table->index('servicio_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_pagos');
    }
};

