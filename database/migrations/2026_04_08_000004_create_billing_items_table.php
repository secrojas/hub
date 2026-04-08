<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('concepto');
            $table->decimal('monto', 12, 2);
            $table->timestamps();
        });

        // Migrate existing billings: create one item per record preserving data
        DB::table('billings')->orderBy('id')->lazyById()->each(function (object $billing) {
            DB::table('billing_items')->insert([
                'billing_id' => $billing->id,
                'task_id'    => null,
                'concepto'   => $billing->concepto,
                'monto'      => $billing->monto,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_items');
    }
};
