<?php

namespace App\Services;

use App\Contracts\Repositories\VarietyRepositoryInterface;
use App\Models\Variety;

class VarietyService
{
    public function __construct(private readonly VarietyRepositoryInterface $varieties) {}
    public function paginate(int $perPage = 20) { return $this->varieties->paginate($perPage); }
    public function active() { return $this->varieties->active(); }
    public function create(array $data): Variety { return $this->varieties->create($data); }
    public function update(Variety $variety, array $data): Variety { return $this->varieties->update($variety, $data); }
    public function delete(Variety $variety): bool { return $this->varieties->delete($variety); }
}
