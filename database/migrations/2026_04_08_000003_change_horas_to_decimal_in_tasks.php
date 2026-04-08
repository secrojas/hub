<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tasks MODIFY horas DECIMAL(5,2) UNSIGNED NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tasks MODIFY horas SMALLINT UNSIGNED NULL DEFAULT NULL');
    }
};
