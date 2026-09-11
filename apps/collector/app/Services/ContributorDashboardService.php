<?php

namespace App\Services;

use App\Enums\CollectionSessionStatus;
use App\Enums\ContributionStatus;
use App\Models\User;

class ContributorDashboardService
{
    public function data(User $user): array
    {
        $user->loadMissing(['profile', 'userProfile', 'projectMembership']);
        $profile = $user->profile;

        if (! $profile) {
            return [
                'stats' => ['contributions' => 0, 'approved' => 0, 'sessions' => 0, 'themes' => 0],
                'activeSession' => null,
                'recentSessions' => collect(),
            ];
        }

        $sessions = $profile->collectionSessions();
        $contributions = $profile->contributions();

        return [
            'stats' => [
                'contributions' => (clone $contributions)->count(),
                'approved' => (clone $contributions)->where('status', ContributionStatus::APPROVED->value)->count(),
                'sessions' => (clone $sessions)->where('status', CollectionSessionStatus::COMPLETED->value)->count(),
                'themes' => (clone $sessions)->where('status', CollectionSessionStatus::COMPLETED->value)->distinct('category_id')->count('category_id'),
            ],
            'activeSession' => (clone $sessions)
                ->with('category')
                ->where('status', CollectionSessionStatus::STARTED->value)
                ->latest('started_at')
                ->first(),
            'recentSessions' => (clone $sessions)
                ->with('category')
                ->withCount('contributions')
                ->latest('started_at')
                ->limit(6)
                ->get(),
        ];
    }
}
