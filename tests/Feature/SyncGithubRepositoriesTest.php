<?php

use App\Models\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('github:sync stores owned repositories and excludes forks', function () {
    config([
        'services.github.username' => 'octocat',
        'services.github.token' => 'test-token',
    ]);

    Http::fake([
        'api.github.com/users/octocat/repos*' => Http::response([
            [
                'id' => 1,
                'name' => 'own-repo',
                'full_name' => 'octocat/own-repo',
                'description' => 'An owned repository',
                'html_url' => 'https://github.com/octocat/own-repo',
                'language' => 'PHP',
                'topics' => ['laravel'],
                'stargazers_count' => 10,
                'forks_count' => 2,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
            ],
            [
                'id' => 2,
                'name' => 'forked-repo',
                'full_name' => 'octocat/forked-repo',
                'description' => 'A forked repository',
                'html_url' => 'https://github.com/octocat/forked-repo',
                'language' => 'JavaScript',
                'topics' => [],
                'stargazers_count' => 0,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => true,
            ],
        ], 200),
        'api.github.com/repos/octocat/own-repo/readme' => Http::response('# Own Repo', 200),
    ]);

    $this->artisan('github:sync')->assertExitCode(0);

    expect(Repository::count())->toBe(1);

    $repository = Repository::sole();
    expect($repository->name)->toBe('own-repo');
    expect($repository->full_name)->toBe('octocat/own-repo');
    expect($repository->readme)->toBe('# Own Repo');
    expect($repository->topics)->toBe(['laravel']);
});

test('github:sync updates an existing repository instead of duplicating it', function () {
    config(['services.github.username' => 'octocat', 'services.github.token' => null]);

    Repository::factory()->create([
        'github_id' => 1,
        'name' => 'own-repo',
        'stargazers_count' => 1,
    ]);

    Http::fake([
        'api.github.com/users/octocat/repos*' => Http::response([
            [
                'id' => 1,
                'name' => 'own-repo',
                'full_name' => 'octocat/own-repo',
                'description' => 'Updated description',
                'html_url' => 'https://github.com/octocat/own-repo',
                'language' => 'PHP',
                'topics' => [],
                'stargazers_count' => 42,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
            ],
        ], 200),
        'api.github.com/repos/octocat/own-repo/readme' => Http::response('# Own Repo', 200),
    ]);

    $this->artisan('github:sync')->assertExitCode(0);

    expect(Repository::count())->toBe(1);
    expect(Repository::sole()->stargazers_count)->toBe(42);
});
