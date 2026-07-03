<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->string('tipo_sala_requerido', 50)->nullable()->after('categoria');
        });

        DB::table('servicios')->where('categoria', 'ESPECIALIDAD')->update(['tipo_sala_requerido' => 'CONSULTORIO']);
        DB::table('servicios')->where('categoria', 'INTERNACION')->update(['tipo_sala_requerido' => 'UCI']);
        DB::table('servicios')->where('categoria', 'ENFERMERIA')->update(['tipo_sala_requerido' => 'CONSULTORIO']);
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn('tipo_sala_requerido');
        });
    }
};
