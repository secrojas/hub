<?php

namespace App\Http\Controllers;

use App\Services\TravelService;
use Inertia\Inertia;
use Inertia\Response;

class PublicTravelController extends Controller
{
    public function __construct(
        private readonly TravelService $service,
    ) {}

    public function show(string $token): Response
    {
        $travel = $this->service->findByShareToken($token);

        return Inertia::render('Travels/Public', [
            'travel' => [
                'titulo'       => $travel->titulo,
                'destino'      => $travel->destino,
                'descripcion'  => $travel->descripcion,
                'fecha_inicio' => $travel->fecha_inicio->format('Y-m-d'),
                'fecha_fin'    => $travel->fecha_fin->format('Y-m-d'),
                'estado'       => $travel->estado->value,
                'estado_label' => $travel->estado->label(),
                'estado_color' => $travel->estado->color(),
                'duracion_dias' => $travel->duracion_dias,
                'segments'     => $travel->segments->map(fn ($s) => [
                    'id'              => $s->id,
                    'tipo'            => $s->tipo->value,
                    'tipo_label'      => $s->tipo->label(),
                    'tipo_icon'       => $s->tipo->icon(),
                    'origen'          => $s->origen,
                    'destino'         => $s->destino,
                    'fecha_salida'    => $s->fecha_salida->format('Y-m-d'),
                    'hora_salida'     => $s->hora_salida,
                    'fecha_llegada'   => $s->fecha_llegada->format('Y-m-d'),
                    'hora_llegada'    => $s->hora_llegada,
                    'empresa'         => $s->empresa,
                    'numero_servicio' => $s->numero_servicio,
                    'numero_asiento'  => $s->numero_asiento,
                    'localizador'     => $s->localizador,
                    'numero_anden'    => $s->numero_anden,
                    'notas'           => $s->notas,
                ]),
                'accommodations' => $travel->accommodations->map(fn ($a) => [
                    'id'             => $a->id,
                    'nombre'         => $a->nombre,
                    'tipo'           => $a->tipo->value,
                    'tipo_label'     => $a->tipo->label(),
                    'tipo_icon'      => $a->tipo->icon(),
                    'direccion'      => $a->direccion,
                    'telefono'       => $a->telefono,
                    'fecha_checkin'  => $a->fecha_checkin->format('Y-m-d'),
                    'hora_checkin'   => $a->hora_checkin,
                    'fecha_checkout' => $a->fecha_checkout->format('Y-m-d'),
                    'hora_checkout'  => $a->hora_checkout,
                    'numero_reserva' => $a->numero_reserva,
                    'notas'          => $a->notas,
                ]),
                'activities' => $travel->activities->map(fn ($act) => [
                    'id'          => $act->id,
                    'fecha'       => $act->fecha->format('Y-m-d'),
                    'hora'        => $act->hora,
                    'titulo'      => $act->titulo,
                    'descripcion' => $act->descripcion,
                    'lugar'       => $act->lugar,
                ]),
            ],
        ]);
    }
}
