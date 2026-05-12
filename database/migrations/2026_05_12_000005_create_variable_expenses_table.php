<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variable_expenses', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->decimal('monto', 14, 2);
            $table->string('descripcion');
            $table->string('categoria')->default('otros');
            $table->string('comprobante_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variable_expenses');
    }
};
