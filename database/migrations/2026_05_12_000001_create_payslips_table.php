<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->string('periodo', 7);           // "2026-04"
            $table->string('empresa');
            $table->date('fecha_pago')->nullable();
            $table->string('banco')->nullable();
            $table->decimal('sueldo_basico', 14, 2)->default(0);
            $table->decimal('total_bruto', 14, 2)->default(0);
            $table->decimal('total_sin_aporte', 14, 2)->default(0);
            $table->decimal('total_descuentos', 14, 2)->default(0);
            $table->decimal('total_neto', 14, 2)->default(0);
            $table->json('conceptos')->nullable();
            $table->string('archivo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
