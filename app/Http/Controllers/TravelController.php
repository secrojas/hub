<?php

namespace App\Http\Controllers;

use App\Enums\TravelEstado;
use App\Http\Requests\StoreTravelRequest;
use App\Http\Requests\UpdateTravelRequest;
use App\Models\Travel;
use App\Services\TravelService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TravelController extends Controller
{
    public function __construct(
        private readonly TravelService $service,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Travels/Index', [
            'travelsByYear' => $this->service->getGroupedByYear()
                ->map(fn ($travels) => $travels->map(fn (Travel $t) => [
                    'id'           => $t->id,
                    'titulo'       => $t->titulo,
                    'destino'      => $t->destino,
                    'fecha_inicio' => $t->fecha_inicio->format('Y-m-d'),
                    'fecha_fin'    => $t->fecha_fin->format('Y-m-d'),
                    'estado'       => $t->estado->value,
                    'estado_label' => $t->estado->label(),
                    'estado_color' => $t->estado->color(),
                    'duracion_dias' => $t->duracion_dias,
                ])),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Travels/Create', [
            'estados' => collect(TravelEstado::cases())->map(fn ($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]),
        ]);
    }

    public function store(StoreTravelRequest $request): RedirectResponse
    {
        $travel = $this->service->create($request->validated());

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Viaje creado correctamente.');
    }

    public function show(Travel $travel): Response
    {
        $travel = $this->service->findById($travel->id);

        return Inertia::render('Admin/Travels/Show', [
            'travel' => [
                'id'           => $travel->id,
                'titulo'       => $travel->titulo,
                'destino'      => $travel->destino,
                'descripcion'  => $travel->descripcion,
                'fecha_inicio' => $travel->fecha_inicio->format('Y-m-d'),
                'fecha_fin'    => $travel->fecha_fin->format('Y-m-d'),
                'estado'       => $travel->estado->value,
                'estado_label' => $travel->estado->label(),
                'estado_color' => $travel->estado->color(),
                'duracion_dias' => $travel->duracion_dias,
                'share_token'  => $travel->share_token,
                'notas'        => $travel->notas,
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
                    'documents_count' => $s->documents->count(),
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
                    'documents_count' => $a->documents->count(),
                ]),
                'activities' => $travel->activities->map(fn ($act) => [
                    'id'          => $act->id,
                    'fecha'       => $act->fecha->format('Y-m-d'),
                    'hora'        => $act->hora,
                    'titulo'      => $act->titulo,
                    'descripcion' => $act->descripcion,
                    'lugar'       => $act->lugar,
                    'notas'       => $act->notas,
                ]),
                'documents' => $travel->documents->map(fn ($d) => [
                    'id'                      => $d->id,
                    'nombre'                  => $d->nombre,
                    'tipo'                    => $d->tipo->value,
                    'tipo_label'              => $d->tipo->label(),
                    'mime_type'               => $d->mime_type,
                    'tamanio_formateado'      => $d->tamanio_formateado,
                    'travel_segment_id'       => $d->travel_segment_id,
                    'travel_accommodation_id' => $d->travel_accommodation_id,
                    'notas'                   => $d->notas,
                    'is_pdf'                  => $d->isPdf(),
                    'is_image'                => $d->isImage(),
                ]),
            ],
        ]);
    }

    public function edit(Travel $travel): Response
    {
        return Inertia::render('Admin/Travels/Edit', [
            'travel' => [
                'id'           => $travel->id,
                'titulo'       => $travel->titulo,
                'destino'      => $travel->destino,
                'descripcion'  => $travel->descripcion,
                'fecha_inicio' => $travel->fecha_inicio->format('Y-m-d'),
                'fecha_fin'    => $travel->fecha_fin->format('Y-m-d'),
                'estado'       => $travel->estado->value,
                'notas'        => $travel->notas,
            ],
            'estados' => collect(TravelEstado::cases())->map(fn ($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]),
        ]);
    }

    public function update(UpdateTravelRequest $request, Travel $travel): RedirectResponse
    {
        $this->service->update($travel, $request->validated());

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Viaje actualizado correctamente.');
    }

    public function destroy(Travel $travel): RedirectResponse
    {
        $this->service->delete($travel);

        return redirect()->route('travels.index')
            ->with('message', 'Viaje eliminado.');
    }
}
