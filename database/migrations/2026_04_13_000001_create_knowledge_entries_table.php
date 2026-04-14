<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_id')->unique();
            $table->string('titulo');
            $table->string('type');
            $table->string('status')->default('draft');
            $table->string('confidence')->default('medium');
            $table->string('source')->default('self');
            $table->boolean('verified')->default(false);
            $table->string('domain')->nullable();
            $table->string('subdomain')->nullable();
            $table->json('tags')->nullable();
            $table->string('scope')->nullable();
            $table->text('summary')->nullable();
            $table->longText('contenido')->nullable();
            $table->string('avature_version')->nullable();
            $table->string('embedding_priority')->default('normal');
            $table->timestamps();

            $table->fullText('summary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_entries');
    }
};
