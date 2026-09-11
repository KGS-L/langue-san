<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProjectApplicationRepositoryInterface;
use App\Models\ProjectApplication;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectApplicationRepository implements ProjectApplicationRepositoryInterface
{
    public function __construct(private readonly ProjectApplication $model) {}

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['user.userProfile', 'reviewer'])
            ->latest()
            ->paginate($perPage);
    }

    public function latestForUser(User $user): ?ProjectApplication
    {
        return $this->model->newQuery()
            ->where('user_id', $user->id)
            ->latest()
            ->first();
    }

    public function create(array $data): ProjectApplication
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(ProjectApplication $application, array $data): ProjectApplication
    {
        $application->update($data);

        return $application->refresh();
    }
}
