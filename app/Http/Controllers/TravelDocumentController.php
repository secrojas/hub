<?php

namespace App\Http\Controllers;

use App\Enums\TravelDocumentTipo;
use App\Models\Travel;
use App\Models\TravelDocument;
use App\Services\TravelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TravelDocumentController extends Controller
{
    public function __construct(
        private readonly TravelService $service,
    ) {}

    public function store(Request $request, Travel $travel): RedirectResponse
    {
        $request->validate([
            'archivo'                 => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,webp'],
            'nombre'                  => ['nullable', 'string', 'max:255'],
            'tipo'                    => ['required', 'string'],
            'travel_segment_id'       => ['nullable', 'integer', 'exists:travel_segments,id'],
            'travel_accommodation_id' => ['nullable', 'integer', 'exists:travel_accommodations,id'],
            'notas'                   => ['nullable', 'string'],
        ]);

        $this->service->uploadDocument(
            $travel,
            $request->file('archivo'),
            $request->only(['nombre', 'tipo', 'travel_segment_id', 'travel_accommodation_id', 'notas']),
        );

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Documento subido correctamente.');
    }

    public function download(Travel $travel, TravelDocument $document): StreamedResponse
    {
        abort_unless($document->travel_id === $travel->id, 404);

        return Storage::disk('local')->download(
            $document->archivo_path,
            $document->nombre,
        );
    }

    public function destroy(Travel $travel, TravelDocument $document): RedirectResponse
    {
        abort_unless($document->travel_id === $travel->id, 404);

        $this->service->deleteDocument($document);

        return redirect()->route('travels.show', $travel)
            ->with('message', 'Documento eliminado.');
    }
}
