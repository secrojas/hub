<?php

namespace App\Services;

use App\Contracts\Repositories\TravelRepositoryInterface;
use App\Models\Travel;
use App\Models\TravelAccommodation;
use App\Models\TravelActivity;
use App\Models\TravelDocument;
use App\Models\TravelSegment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Storage;

class TravelService
{
    public function __construct(
        private readonly TravelRepositoryInterface $repository,
    ) {}

    public function getGroupedByYear(): SupportCollection
    {
        return $this->repository->getGroupedByYear();
    }

    public function findById(int $id): Travel
    {
        return $this->repository->findById($id);
    }

    public function findByShareToken(string $token): Travel
    {
        return $this->repository->findByShareToken($token);
    }

    public function create(array $data): Travel
    {
        return $this->repository->create($data);
    }

    public function update(Travel $travel, array $data): Travel
    {
        return $this->repository->update($travel, $data);
    }

    public function delete(Travel $travel): void
    {
        $this->deleteAllDocumentFiles($travel);
        $this->repository->delete($travel);
    }

    // --- Segments ---

    public function addSegment(Travel $travel, array $data): TravelSegment
    {
        return $travel->segments()->create($data);
    }

    public function updateSegment(TravelSegment $segment, array $data): TravelSegment
    {
        $segment->update($data);

        return $segment->fresh();
    }

    public function deleteSegment(TravelSegment $segment): void
    {
        $segment->delete();
    }

    // --- Accommodations ---

    public function addAccommodation(Travel $travel, array $data): TravelAccommodation
    {
        return $travel->accommodations()->create($data);
    }

    public function updateAccommodation(TravelAccommodation $accommodation, array $data): TravelAccommodation
    {
        $accommodation->update($data);

        return $accommodation->fresh();
    }

    public function deleteAccommodation(TravelAccommodation $accommodation): void
    {
        $accommodation->delete();
    }

    // --- Activities ---

    public function addActivity(Travel $travel, array $data): TravelActivity
    {
        return $travel->activities()->create($data);
    }

    public function updateActivity(TravelActivity $activity, array $data): TravelActivity
    {
        $activity->update($data);

        return $activity->fresh();
    }

    public function deleteActivity(TravelActivity $activity): void
    {
        $activity->delete();
    }

    // --- Documents ---

    public function uploadDocument(
        Travel $travel,
        UploadedFile $file,
        array $data,
    ): TravelDocument {
        $path = $file->store("personal/travels/{$travel->id}", 'local');

        return $travel->documents()->create([
            'nombre'                  => $data['nombre'] ?? $file->getClientOriginalName(),
            'tipo'                    => $data['tipo'],
            'archivo_path'            => $path,
            'mime_type'               => $file->getMimeType(),
            'tamanio'                 => $file->getSize(),
            'travel_segment_id'       => $data['travel_segment_id'] ?? null,
            'travel_accommodation_id' => $data['travel_accommodation_id'] ?? null,
            'notas'                   => $data['notas'] ?? null,
        ]);
    }

    public function deleteDocument(TravelDocument $document): void
    {
        Storage::disk('local')->delete($document->archivo_path);
        $document->delete();
    }

    private function deleteAllDocumentFiles(Travel $travel): void
    {
        foreach ($travel->documents as $document) {
            Storage::disk('local')->delete($document->archivo_path);
        }
    }
}
