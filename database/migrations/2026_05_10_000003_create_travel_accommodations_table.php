<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_id')->constrained('travels')->cascadeOnDelete();
            $table->string('nombre');
            $table->enum('tipo', ['hotel', 'hostel', 'airbnb', 'casa', 'camping', 'otro'])->default('hotel');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->date('fecha_checkin');
            $table->time('hora_checkin')->nullable();
            $table->date('fecha_checkout');
            $table->time('hora_checkout')->nullable();
            $table->string('numero_reserva')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_accommodations');
    }
};
