<?php

namespace App\Contracts\Repositories;

use App\Models\Recording;

interface RecordingRepositoryInterface
{
    public function create(array $data): Recording;
}
