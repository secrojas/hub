<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('empresa')->nullable();
            $table->string('telefono')->nullable();
            $table->text('stack_tecnologico')->nullable();
            $table->enum('estado', ['activo', 'potencial', 'pausado'])->default('activo');
            $table->text('notas')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
