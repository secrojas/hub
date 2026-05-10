<?php

namespace App\Http\Controllers;

use App\Models\Travel;
use App\Models\TravelActivity;
use App\Services\TravelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TravelActivityController extends Controller
{
    public function __construct(
        private readonly TravelService $service,
    ) {}

    public function create(Travel $travel): Response
    {
        return Inertia::render('Admin/Travels/Activities/Create', [
            'travel' => ['id' => $travel->id, 'titulo' => $travel->titulo, 'destino' => $travel->destino],
        ]);
    }

    public function store(Request $request, Travel $travel): RedirectResponse
    {
        $data = $request->validate([
            'fecha'       => ['required', 'date'],
            'hora'        => ['nullable', 'date_format:H:i'],
            'titulo'      => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'lugar'       => ['nullable', 'string', 'max:255'],
            'notas'       => ['nullable', 'string'],
        ]);

        $this->service->addActivity($travel, $data);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Actividad agregada correctamente.');
    }

    public function edit(Travel $travel, TravelActivity $activity): Response
    {
        return Inertia::render('Admin/Travels/Activities/Edit', [
            'travel'   => ['id' => $travel->id, 'titulo' => $travel->titulo, 'destino' => $travel->destino],
            'activity' => $activity,
        ]);
    }

    public function update(Request $request, Travel $travel, TravelActivity $activity): RedirectResponse
    {
        $data = $request->validate([
            'fecha'       => ['required', 'date'],
            'hora'        => ['nullable', 'date_format:H:i'],
            'titulo'      => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'lugar'       => ['nullable', 'string', 'max:255'],
            'notas'       => ['nullable', 'string'],
        ]);

        $this->service->updateActivity($activity, $data);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Actividad actualizada correctamente.');
    }

    public function destroy(Travel $travel, TravelActivity $activity): RedirectResponse
    {
        $this->service->deleteActivity($activity);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Actividad eliminada.');
    }
}
