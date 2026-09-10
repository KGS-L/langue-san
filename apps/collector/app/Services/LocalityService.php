<?php

namespace App\Services;

use App\Contracts\Repositories\LocalityRepositoryInterface;
use App\Models\Locality;

class LocalityService
{
    public function __construct(private readonly LocalityRepositoryInterface $localities) {}
    public function paginate(int $perPage = 20) { return $this->localities->paginate($perPage); }
    public function active() { return $this->localities->active(); }
    public function create(array $data): Locality { return $this->localities->create($data); }
    public function update(Locality $locality, array $data): Locality { return $this->localities->update($locality, $data); }
    public function delete(Locality $locality): bool { return $this->localities->delete($locality); }
}
