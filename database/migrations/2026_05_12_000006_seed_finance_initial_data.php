<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bank_accounts')->insert([
            ['nombre' => 'Santander',     'tipo' => 'caja_ahorro',       'color' => 'red',   'orden' => 1, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Provincia',     'tipo' => 'caja_ahorro',       'color' => 'blue',  'orden' => 2, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lemon',         'tipo' => 'billetera_digital', 'color' => 'amber', 'orden' => 3, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Mercado Pago',  'tipo' => 'billetera_digital', 'color' => 'sky',   'orden' => 4, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('fixed_expenses')->insert([
            ['nombre' => 'Alquiler',           'monto' => 0, 'dia_vencimiento' => 1,    'categoria' => 'alquiler',      'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cochera',            'monto' => 0, 'dia_vencimiento' => null,  'categoria' => 'cochera',       'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Luz',                'monto' => 0, 'dia_vencimiento' => null,  'categoria' => 'servicios',     'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Agua',               'monto' => 0, 'dia_vencimiento' => null,  'categoria' => 'servicios',     'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Gas',                'monto' => 0, 'dia_vencimiento' => null,  'categoria' => 'servicios',     'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Internet',           'monto' => 0, 'dia_vencimiento' => null,  'categoria' => 'servicios',     'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tarjeta Santander',  'monto' => 0, 'dia_vencimiento' => null,  'categoria' => 'tarjetas',      'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tarjeta Provincia',  'monto' => 0, 'dia_vencimiento' => null,  'categoria' => 'tarjetas',      'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tarjeta Mercado Pago','monto' => 0,'dia_vencimiento' => null,  'categoria' => 'tarjetas',      'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cuota Running',      'monto' => 0, 'dia_vencimiento' => null,  'categoria' => 'suscripciones', 'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Crédito Provincia',  'monto' => 0, 'dia_vencimiento' => null,  'categoria' => 'credito',       'activo' => true, 'descripcion' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('fixed_expenses')->truncate();
        DB::table('bank_accounts')->truncate();
    }
};
