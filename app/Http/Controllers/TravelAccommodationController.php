<?php

namespace App\Http\Controllers;

use App\Enums\TravelAccommodationTipo;
use App\Models\Travel;
use App\Models\TravelAccommodation;
use App\Services\TravelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TravelAccommodationController extends Controller
{
    public function __construct(
        private readonly TravelService $service,
    ) {}

    public function create(Travel $travel): Response
    {
        return Inertia::render('Admin/Travels/Accommodations/Create', [
            'travel' => ['id' => $travel->id, 'titulo' => $travel->titulo, 'destino' => $travel->destino],
            'tipos'  => collect(TravelAccommodationTipo::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'icon'  => $t->icon(),
            ]),
        ]);
    }

    public function store(Request $request, Travel $travel): RedirectResponse
    {
        $data = $request->validate([
            'nombre'         => ['required', 'string', 'max:255'],
            'tipo'           => ['required', 'string'],
            'direccion'      => ['nullable', 'string', 'max:255'],
            'telefono'       => ['nullable', 'string', 'max:50'],
            'fecha_checkin'  => ['required', 'date'],
            'hora_checkin'   => ['nullable', 'date_format:H:i'],
            'fecha_checkout' => ['required', 'date', 'after_or_equal:fecha_checkin'],
            'hora_checkout'  => ['nullable', 'date_format:H:i'],
            'numero_reserva' => ['nullable', 'string', 'max:255'],
            'notas'          => ['nullable', 'string'],
        ]);

        $this->service->addAccommodation($travel, $data);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Hospedaje agregado correctamente.');
    }

    public function edit(Travel $travel, TravelAccommodation $accommodation): Response
    {
        return Inertia::render('Admin/Travels/Accommodations/Edit', [
            'travel'        => ['id' => $travel->id, 'titulo' => $travel->titulo, 'destino' => $travel->destino],
            'accommodation' => $accommodation,
            'tipos'         => collect(TravelAccommodationTipo::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'icon'  => $t->icon(),
            ]),
        ]);
    }

    public function update(Request $request, Travel $travel, TravelAccommodation $accommodation): RedirectResponse
    {
        $data = $request->validate([
            'nombre'         => ['required', 'string', 'max:255'],
            'tipo'           => ['required', 'string'],
            'direccion'      => ['nullable', 'string', 'max:255'],
            'telefono'       => ['nullable', 'string', 'max:50'],
            'fecha_checkin'  => ['required', 'date'],
            'hora_checkin'   => ['nullable', 'date_format:H:i'],
            'fecha_checkout' => ['required', 'date', 'after_or_equal:fecha_checkin'],
            'hora_checkout'  => ['nullable', 'date_format:H:i'],
            'numero_reserva' => ['nullable', 'string', 'max:255'],
            'notas'          => ['nullable', 'string'],
        ]);

        $this->service->updateAccommodation($accommodation, $data);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Hospedaje actualizado correctamente.');
    }

    public function destroy(Travel $travel, TravelAccommodation $accommodation): RedirectResponse
    {
        $this->service->deleteAccommodation($accommodation);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Hospedaje eliminado.');
    }
}
