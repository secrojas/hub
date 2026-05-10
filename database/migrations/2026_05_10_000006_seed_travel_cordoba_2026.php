<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if already seeded (local dev has it from tinker)
        if (DB::table('travels')->where('share_token', '750e44d9-41ae-4150-9dde-f3f117e33869')->exists()) {
            return;
        }

        $travelId = DB::table('travels')->insertGetId([
            'titulo'       => 'Cordoba 2026 - Capilla del Monte',
            'destino'      => 'Capilla del Monte',
            'descripcion'  => 'Viaje a Capilla del Monte, Cordoba, con la idea de conocer San Marcos Sierra tambien.',
            'fecha_inicio' => '2026-05-22',
            'fecha_fin'    => '2026-05-25',
            'estado'       => 'planificado',
            'share_token'  => '750e44d9-41ae-4150-9dde-f3f117e33869',
            'notas'        => 'Organizando, pasajes comprado.',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $segments = [
            [
                'orden'           => 1,
                'tipo'            => 'micro',
                'origen'          => 'Mar del Plata',
                'destino'         => 'Córdoba',
                'fecha_salida'    => '2026-05-22',
                'hora_salida'     => '16:45:00',
                'fecha_llegada'   => '2026-05-23',
                'hora_llegada'    => '06:15:00',
                'empresa'         => 'Buses LEP',
                'numero_servicio' => 'ET-272',
                'numero_asiento'  => 'Butaca 39 (Seba) · 40 (Nadia)',
                'localizador'     => '@-041925336 / @-041925337',
                'notas'           => 'BOL 9948581-0129900034219 (Seba) · BOL 9948582-0129900034220 (Nadia)',
            ],
            [
                'orden'           => 2,
                'tipo'            => 'tren',
                'origen'          => 'Córdoba (TDS Mitre)',
                'destino'         => 'Cosquín',
                'fecha_salida'    => '2026-05-23',
                'hora_salida'     => '13:15:00',
                'fecha_llegada'   => '2026-05-23',
                'hora_llegada'    => null,
                'empresa'         => 'Trenes Argentinos — Tren de las Sierras',
                'numero_servicio' => 'Tren 2108 · Coche 701',
                'numero_asiento'  => 'Asiento 33 (Nadia) · 34 (Seba)',
                'localizador'     => 'N° 17142445 (Nadia) · 17142446 (Seba)',
                'notas'           => 'Sec. 34167297 (Nadia) · 34167298 (Seba)',
            ],
            [
                'orden'           => 3,
                'tipo'            => 'tren',
                'origen'          => 'Cosquín',
                'destino'         => 'Capilla del Monte',
                'fecha_salida'    => '2026-05-23',
                'hora_salida'     => '16:22:00',
                'fecha_llegada'   => '2026-05-23',
                'hora_llegada'    => null,
                'empresa'         => 'Trenes Argentinos — Tren de las Sierras',
                'numero_servicio' => 'Tren 2110 · Coche 701',
                'numero_asiento'  => 'Asiento 33 (Nadia) · 34 (Seba)',
                'localizador'     => 'N° 17142447 (Nadia) · 17142448 (Seba)',
                'notas'           => 'Sec. 34167299 (Nadia) · 34167300 (Seba)',
            ],
            [
                'orden'           => 4,
                'tipo'            => 'micro',
                'origen'          => 'Capilla del Monte',
                'destino'         => 'Mar del Plata',
                'fecha_salida'    => '2026-05-24',
                'hora_salida'     => '18:45:00',
                'fecha_llegada'   => '2026-05-25',
                'hora_llegada'    => '11:15:00',
                'empresa'         => 'Buses LEP',
                'numero_servicio' => 'ET-270',
                'numero_asiento'  => 'Butaca 17 (Nadia) · 18 (Seba)',
                'localizador'     => '@-041925417 / @-041925418',
                'notas'           => 'BOL 9948624-0130000035556 (Nadia) · BOL 9948625-0130000035557 (Seba)',
            ],
        ];

        foreach ($segments as $segment) {
            DB::table('travel_segments')->insert(array_merge($segment, [
                'travel_id'  => $travelId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        $travel = DB::table('travels')
            ->where('share_token', '750e44d9-41ae-4150-9dde-f3f117e33869')
            ->first();

        if ($travel) {
            DB::table('travel_segments')->where('travel_id', $travel->id)->delete();
            DB::table('travels')->where('id', $travel->id)->delete();
        }
    }
};
