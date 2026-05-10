<?php

namespace App\Http\Controllers;

use App\Enums\TravelSegmentTipo;
use App\Models\Travel;
use App\Models\TravelSegment;
use App\Services\TravelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TravelSegmentController extends Controller
{
    public function __construct(
        private readonly TravelService $service,
    ) {}

    public function create(Travel $travel): Response
    {
        return Inertia::render('Admin/Travels/Segments/Create', [
            'travel' => ['id' => $travel->id, 'titulo' => $travel->titulo, 'destino' => $travel->destino],
            'tipos'  => collect(TravelSegmentTipo::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'icon'  => $t->icon(),
            ]),
        ]);
    }

    public function store(Request $request, Travel $travel): RedirectResponse
    {
        $data = $request->validate([
            'tipo'            => ['required', 'string'],
            'origen'          => ['required', 'string', 'max:255'],
            'destino'         => ['required', 'string', 'max:255'],
            'fecha_salida'    => ['required', 'date'],
            'hora_salida'     => ['nullable', 'date_format:H:i'],
            'fecha_llegada'   => ['required', 'date', 'after_or_equal:fecha_salida'],
            'hora_llegada'    => ['nullable', 'date_format:H:i'],
            'empresa'         => ['nullable', 'string', 'max:255'],
            'numero_servicio' => ['nullable', 'string', 'max:255'],
            'numero_asiento'  => ['nullable', 'string', 'max:100'],
            'localizador'     => ['nullable', 'string', 'max:100'],
            'numero_anden'    => ['nullable', 'string', 'max:100'],
            'notas'           => ['nullable', 'string'],
        ]);

        $this->service->addSegment($travel, $data);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Tramo agregado correctamente.');
    }

    public function edit(Travel $travel, TravelSegment $segment): Response
    {
        return Inertia::render('Admin/Travels/Segments/Edit', [
            'travel'  => ['id' => $travel->id, 'titulo' => $travel->titulo, 'destino' => $travel->destino],
            'segment' => $segment,
            'tipos'   => collect(TravelSegmentTipo::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'icon'  => $t->icon(),
            ]),
        ]);
    }

    public function update(Request $request, Travel $travel, TravelSegment $segment): RedirectResponse
    {
        $data = $request->validate([
            'tipo'            => ['required', 'string'],
            'origen'          => ['required', 'string', 'max:255'],
            'destino'         => ['required', 'string', 'max:255'],
            'fecha_salida'    => ['required', 'date'],
            'hora_salida'     => ['nullable', 'date_format:H:i'],
            'fecha_llegada'   => ['required', 'date', 'after_or_equal:fecha_salida'],
            'hora_llegada'    => ['nullable', 'date_format:H:i'],
            'empresa'         => ['nullable', 'string', 'max:255'],
            'numero_servicio' => ['nullable', 'string', 'max:255'],
            'numero_asiento'  => ['nullable', 'string', 'max:100'],
            'localizador'     => ['nullable', 'string', 'max:100'],
            'numero_anden'    => ['nullable', 'string', 'max:100'],
            'notas'           => ['nullable', 'string'],
        ]);

        $this->service->updateSegment($segment, $data);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Tramo actualizado correctamente.');
    }

    public function destroy(Travel $travel, TravelSegment $segment): RedirectResponse
    {
        $this->service->deleteSegment($segment);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Tramo eliminado.');
    }
}
