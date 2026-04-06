<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedSmallInteger('horas')->nullable()->after('fecha_limite');
            $table->timestamp('fecha_finalizacion')->nullable()->after('horas');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('valor_hora', 10, 2)->nullable()->after('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['horas', 'fecha_finalizacion']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('valor_hora');
        });
    }
};
