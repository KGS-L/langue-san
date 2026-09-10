<?php

namespace App\Contracts\Repositories;

use App\Models\Locality;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface LocalityRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;
    public function active(): Collection;
    public function create(array $data): Locality;
    public function update(Locality $locality, array $data): Locality;
    public function delete(Locality $locality): bool;
}
