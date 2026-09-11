<?php

namespace App\Contracts\Repositories;

use App\Models\ProjectApplication;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectApplicationRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;
    public function latestForUser(User $user): ?ProjectApplication;
    public function create(array $data): ProjectApplication;
    public function update(ProjectApplication $application, array $data): ProjectApplication;
}
