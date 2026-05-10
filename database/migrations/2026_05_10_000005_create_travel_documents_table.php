<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_id')->constrained('travels')->cascadeOnDelete();
            $table->foreignId('travel_segment_id')->nullable()->constrained('travel_segments')->nullOnDelete();
            $table->foreignId('travel_accommodation_id')->nullable()->constrained('travel_accommodations')->nullOnDelete();
            $table->string('nombre');
            $table->enum('tipo', ['pasaje', 'reserva', 'voucher', 'foto', 'otro'])->default('otro');
            $table->string('archivo_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('tamanio')->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_documents');
    }
};
