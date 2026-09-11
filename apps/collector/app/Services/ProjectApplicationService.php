<?php

namespace App\Services;

use App\Contracts\Repositories\ProjectApplicationRepositoryInterface;
use App\Contracts\Repositories\ProjectMembershipRepositoryInterface;
use App\Enums\ProjectApplicationStatus;
use App\Mail\ProjectApplicationDecisionMail;
use App\Models\ProjectApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ProjectApplicationService
{
    public function __construct(
        private readonly ProjectApplicationRepositoryInterface $applications,
        private readonly ProjectMembershipRepositoryInterface $memberships,
    ) {}

    public function paginate(int $perPage = 20)
    {
        return $this->applications->paginate($perPage);
    }

    public function latestForUser(User $user): ?ProjectApplication
    {
        return $this->applications->latestForUser($user);
    }

    public function submit(User $user, array $data): ProjectApplication
    {
        $latest = $this->applications->latestForUser($user);

        if ($latest && in_array($latest->status, [
            ProjectApplicationStatus::PENDING,
            ProjectApplicationStatus::UNDER_REVIEW,
        ], true)) {
            throw ValidationException::withMessages([
                'application' => 'Vous avez déjà une candidature en cours d’examen.',
            ]);
        }

        return $this->applications->create([
            'user_id' => $user->id,
            'contribution_areas' => array_values($data['contribution_areas']),
            'experience' => $data['experience'],
            'motivation' => $data['motivation'],
            'availability' => $data['availability'] ?? null,
            'portfolio_url' => $data['portfolio_url'] ?? null,
            'san_connection' => $data['san_connection'] ?? null,
            'status' => ProjectApplicationStatus::PENDING,
        ]);
    }

    public function review(
        ProjectApplication $application,
        User $reviewer,
        ProjectApplicationStatus $decision,
        ?string $reason = null,
    ): ProjectApplication {
        if (! in_array($decision, [ProjectApplicationStatus::APPROVED, ProjectApplicationStatus::REJECTED], true)) {
            throw ValidationException::withMessages([
                'decision' => 'Décision invalide.',
            ]);
        }

        $updated = DB::transaction(function () use ($application, $reviewer, $decision, $reason): ProjectApplication {
            $updated = $this->applications->update($application, [
                'status' => $decision,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'decision_reason' => $reason,
            ]);

            if ($decision === ProjectApplicationStatus::APPROVED) {
                $this->memberships->updateOrCreateForUser($application->user, [
                    'approved_application_id' => $application->id,
                    'contribution_areas' => $application->contribution_areas,
                    'is_active' => true,
                    'started_at' => now(),
                ]);
            }

            return $updated;
        });

        Mail::to($application->user->email)->send(new ProjectApplicationDecisionMail(
            name: $application->user->name,
            status: $decision,
            reason: $reason,
            accountUrl: route('contributor.auth.show'),
        ));

        return $updated;
    }
}
