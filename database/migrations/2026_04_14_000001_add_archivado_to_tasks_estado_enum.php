<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tasks MODIFY COLUMN estado ENUM('backlog','en_progreso','en_revision','finalizado','archivado') NOT NULL DEFAULT 'backlog'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tasks MODIFY COLUMN estado ENUM('backlog','en_progreso','en_revision','finalizado') NOT NULL DEFAULT 'backlog'");
    }
};
