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
        // Tabla para tokens de recuperación de contraseña
        Schema::create('tokens_recuperacion', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // sessions eliminada - se usa SESSION_DRIVER=file
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // sessions eliminada - se usa SESSION_DRIVER=file
        Schema::dropIfExists('tokens_recuperacion');
    }
};

