<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->string('afip_pdf_path')->nullable()->after('estado');
            $table->timestamp('afip_uploaded_at')->nullable()->after('afip_pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn(['afip_pdf_path', 'afip_uploaded_at']);
        });
    }
};
