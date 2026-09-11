<?php

namespace App\Providers;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\CollectionSessionRepositoryInterface;
use App\Contracts\Repositories\ContributionRepositoryInterface;
use App\Contracts\Repositories\ContributorConsentRepositoryInterface;
use App\Contracts\Repositories\ContributorProfileRepositoryInterface;
use App\Contracts\Repositories\LocalityRepositoryInterface;
use App\Contracts\Repositories\ProjectApplicationRepositoryInterface;
use App\Contracts\Repositories\ProjectMembershipRepositoryInterface;
use App\Contracts\Repositories\PromptRepositoryInterface;
use App\Contracts\Repositories\RecordingRepositoryInterface;
use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\ValidationRepositoryInterface;
use App\Contracts\Repositories\VarietyRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CollectionSessionRepository;
use App\Repositories\Eloquent\ContributionRepository;
use App\Repositories\Eloquent\ContributorConsentRepository;
use App\Repositories\Eloquent\ContributorProfileRepository;
use App\Repositories\Eloquent\LocalityRepository;
use App\Repositories\Eloquent\ProjectApplicationRepository;
use App\Repositories\Eloquent\ProjectMembershipRepository;
use App\Repositories\Eloquent\PromptRepository;
use App\Repositories\Eloquent\RecordingRepository;
use App\Repositories\Eloquent\UserProfileRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\ValidationRepository;
use App\Repositories\Eloquent\VarietyRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        UserProfileRepositoryInterface::class => UserProfileRepository::class,
        ProjectApplicationRepositoryInterface::class => ProjectApplicationRepository::class,
        ProjectMembershipRepositoryInterface::class => ProjectMembershipRepository::class,
        ContributorProfileRepositoryInterface::class => ContributorProfileRepository::class,
        ContributorConsentRepositoryInterface::class => ContributorConsentRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        LocalityRepositoryInterface::class => LocalityRepository::class,
        VarietyRepositoryInterface::class => VarietyRepository::class,
        PromptRepositoryInterface::class => PromptRepository::class,
        CollectionSessionRepositoryInterface::class => CollectionSessionRepository::class,
        ContributionRepositoryInterface::class => ContributionRepository::class,
        RecordingRepositoryInterface::class => RecordingRepository::class,
        ValidationRepositoryInterface::class => ValidationRepository::class,
    ];
}
