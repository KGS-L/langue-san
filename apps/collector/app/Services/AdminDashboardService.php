<?php

namespace App\Services;

use App\Enums\ContributionStatus;
use App\Enums\ProjectApplicationStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Contribution;
use App\Models\ProjectApplication;
use App\Models\Prompt;
use App\Models\User;

class AdminDashboardService
{
    public function data(): array
    {
        $validationStatuses = [
            ContributionStatus::TRANSCRIBED->value,
            ContributionStatus::VALIDATED_ONCE->value,
            ContributionStatus::VALIDATED_TWICE->value,
        ];

        $total = Contribution::query()->count();
        $approved = Contribution::query()->where('status', ContributionStatus::APPROVED->value)->count();

        $recentContributions = Contribution::query()
            ->with(['prompt.category', 'locality', 'contributorProfile.user'])
            ->latest('submitted_at')
            ->limit(8)
            ->get();

        $underCoveredPrompts = Prompt::query()
            ->where('is_active', true)
            ->with('category')
            ->withCount('contributions')
            ->having('contributions_count', '<', 3)
            ->orderBy('contributions_count')
            ->orderByDesc('priority')
            ->limit(8)
            ->get();

        $topLocalities = Contribution::query()
            ->selectRaw('locality_id, COUNT(*) as total')
            ->whereNotNull('locality_id')
            ->with('locality:id,name')
            ->groupBy('locality_id')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'stats' => [
                'contributors' => User::role(UserRole::CONTRIBUTOR->value)->count(),
                'categories' => Category::query()->count(),
                'prompts' => Prompt::query()->count(),
                'contributions' => $total,
                'today' => Contribution::query()->whereDate('submitted_at', today())->count(),
                'last7days' => Contribution::query()->where('submitted_at', '>=', now()->subDays(7))->count(),
                'toTranscribe' => Contribution::query()->where('status', ContributionStatus::PENDING->value)->count(),
                'toValidate' => Contribution::query()->whereIn('status', $validationStatuses)->count(),
                'approved' => $approved,
                'rejected' => Contribution::query()->where('status', ContributionStatus::REJECTED->value)->count(),
                'approvalRate' => $total > 0 ? round(($approved / $total) * 100, 1) : 0.0,
                'pendingApplications' => ProjectApplication::query()
                    ->whereIn('status', [
                        ProjectApplicationStatus::PENDING->value,
                        ProjectApplicationStatus::UNDER_REVIEW->value,
                    ])->count(),
            ],
            'recentContributions' => $recentContributions,
            'underCoveredPrompts' => $underCoveredPrompts,
            'topLocalities' => $topLocalities,
            'integrations' => [
                'resend' => filled(config('services.resend.key')),
                'google' => filled(config('services.google.client_id')) && filled(config('services.google.client_secret')),
                'mailDriver' => config('mail.default'),
            ],
        ];
    }
}
