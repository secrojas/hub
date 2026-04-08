<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('note_folders')->nullOnDelete();
            $table->string('titulo');
            $table->longText('contenido')->nullable();
            $table->string('extracto', 300)->nullable();
            $table->boolean('esta_fijada')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
