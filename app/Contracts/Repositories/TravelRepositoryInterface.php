<?php

namespace App\Contracts\Repositories;

use App\Models\Travel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface TravelRepositoryInterface
{
    public function getAll(): Collection;
    public function getGroupedByYear(): SupportCollection;
    public function findById(int $id): Travel;
    public function findByShareToken(string $token): Travel;
    public function create(array $data): Travel;
    public function update(Travel $travel, array $data): Travel;
    public function delete(Travel $travel): void;
}
