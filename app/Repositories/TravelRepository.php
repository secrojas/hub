<?php

namespace App\Repositories;

use App\Contracts\Repositories\TravelRepositoryInterface;
use App\Models\Travel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class TravelRepository implements TravelRepositoryInterface
{
    public function getAll(): Collection
    {
        return Travel::orderByDesc('fecha_inicio')->get();
    }

    public function getGroupedByYear(): SupportCollection
    {
        return $this->getAll()
            ->groupBy(fn (Travel $t) => $t->fecha_inicio->year)
            ->sortKeysDesc();
    }

    public function findById(int $id): Travel
    {
        return Travel::with([
            'segments',
            'accommodations',
            'activities',
            'documents.segment',
            'documents.accommodation',
        ])->findOrFail($id);
    }

    public function findByShareToken(string $token): Travel
    {
        return Travel::with([
            'segments',
            'accommodations',
            'activities',
        ])->where('share_token', $token)->firstOrFail();
    }

    public function create(array $data): Travel
    {
        return Travel::create($data);
    }

    public function update(Travel $travel, array $data): Travel
    {
        $travel->update($data);

        return $travel->fresh();
    }

    public function delete(Travel $travel): void
    {
        $travel->delete();
    }
}
