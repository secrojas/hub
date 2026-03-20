<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('prioridad', ['baja', 'media', 'alta'])->default('media');
            $table->enum('estado', ['backlog', 'en_progreso', 'en_revision', 'finalizado'])->default('backlog');
            $table->date('fecha_limite')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
