<?php

namespace App\Providers;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ContributionRepositoryInterface;
use App\Contracts\Repositories\LocalityRepositoryInterface;
use App\Contracts\Repositories\PromptRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\ValidationRepositoryInterface;
use App\Contracts\Repositories\VarietyRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ContributionRepository;
use App\Repositories\Eloquent\LocalityRepository;
use App\Repositories\Eloquent\PromptRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\ValidationRepository;
use App\Repositories\Eloquent\VarietyRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        LocalityRepositoryInterface::class => LocalityRepository::class,
        VarietyRepositoryInterface::class => VarietyRepository::class,
        PromptRepositoryInterface::class => PromptRepository::class,
        ContributionRepositoryInterface::class => ContributionRepository::class,
        ValidationRepositoryInterface::class => ValidationRepository::class,
    ];
}
