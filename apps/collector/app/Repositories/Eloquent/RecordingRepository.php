<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\RecordingRepositoryInterface;
use App\Models\Recording;

class RecordingRepository implements RecordingRepositoryInterface
{
    public function __construct(private readonly Recording $model) {}

    public function create(array $data): Recording
    {
        return $this->model->newQuery()->create($data);
    }
}
