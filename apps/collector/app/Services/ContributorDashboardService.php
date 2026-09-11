<?php

namespace App\Services;

use App\Enums\CollectionSessionStatus;
use App\Enums\ContributionStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContributorDashboardService
{
    public function data(User $user): array
    {
        $user->loadMissing(['profile', 'userProfile', 'projectMembership']);
        $profile = $user->profile;

        if (! $profile) {
            return [
                'stats' => [
                    'contributions' => 0,
                    'approved' => 0,
                    'inReview' => 0,
                    'sessions' => 0,
                    'themes' => 0,
                ],
                'activeSession' => null,
                'recentSessions' => collect(),
                'recentContributions' => collect(),
            ];
        }

        $sessions = $profile->collectionSessions();
        $contributions = $profile->contributions();

        return [
            'stats' => [
                'contributions' => (clone $contributions)->count(),
                'approved' => (clone $contributions)->where('status', ContributionStatus::APPROVED->value)->count(),
                'inReview' => (clone $contributions)->whereIn('status', [
                    ContributionStatus::TRANSCRIBED->value,
                    ContributionStatus::VALIDATED_ONCE->value,
                    ContributionStatus::VALIDATED_TWICE->value,
                ])->count(),
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
            'recentContributions' => (clone $contributions)
                ->with(['prompt.category'])
                ->latest('submitted_at')
                ->limit(6)
                ->get(),
        ];
    }

    public function history(User $user, ?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        $user->loadMissing('profile');
        $profile = $user->profile;

        if (! $profile) {
            return $user->contributions()->whereRaw('1 = 0')->paginate($perPage);
        }

        $query = $profile->contributions()
            ->with(['prompt.category'])
            ->latest('submitted_at');

        if ($status && ContributionStatus::tryFrom($status)) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
