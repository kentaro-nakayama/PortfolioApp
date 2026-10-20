<?php

namespace App\Providers;

use App\Services\GitHub\GitHubClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GitHubClient::class, fn () => new GitHubClient(
            username: config('services.github.username'),
            token: config('services.github.token'),
            excludedRepositories: config('services.github.excluded_repositories', []),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
