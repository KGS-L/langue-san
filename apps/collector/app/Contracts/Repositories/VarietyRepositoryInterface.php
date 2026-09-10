<?php

namespace App\Contracts\Repositories;

use App\Models\Variety;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface VarietyRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;
    public function active(): Collection;
    public function create(array $data): Variety;
    public function update(Variety $variety, array $data): Variety;
    public function delete(Variety $variety): bool;
}
