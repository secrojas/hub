<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_id')->constrained('travels')->cascadeOnDelete();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->enum('tipo', ['avion', 'tren', 'micro', 'barco', 'auto', 'otro']);
            $table->string('origen');
            $table->string('destino');
            $table->date('fecha_salida');
            $table->time('hora_salida')->nullable();
            $table->date('fecha_llegada');
            $table->time('hora_llegada')->nullable();
            $table->string('empresa')->nullable();
            $table->string('numero_servicio')->nullable();
            $table->string('numero_asiento')->nullable();
            $table->string('localizador')->nullable();
            $table->string('numero_anden')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_segments');
    }
};
