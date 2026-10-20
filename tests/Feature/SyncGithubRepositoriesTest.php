<?php

use App\Models\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Any request that is not explicitly faked must fail the test instead of
    // reaching the real GitHub API.
    Http::preventStrayRequests();
});

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
        'api.github.com/repos/octocat/own-repo/readme' => Http::response("# Own Repo\n\n![screenshot](docs/screenshot.png)", 200),
    ]);

    $this->artisan('github:sync')->assertExitCode(0);

    expect(Repository::count())->toBe(1);

    $repository = Repository::sole();
    expect($repository->name)->toBe('own-repo');
    expect($repository->full_name)->toBe('octocat/own-repo');
    expect($repository->readme)->toBe("# Own Repo\n\n![screenshot](docs/screenshot.png)");
    expect($repository->topics)->toBe(['laravel']);
    expect($repository->screenshot_url)->toBe('https://raw.githubusercontent.com/octocat/own-repo/main/docs/screenshot.png');
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

test('github:sync excludes private and internal repositories from the API response', function () {
    config(['services.github.username' => 'octocat', 'services.github.token' => 'test-token']);

    Http::fake([
        'api.github.com/users/octocat/repos*' => Http::response([
            [
                'id' => 1,
                'name' => 'public-repo',
                'full_name' => 'octocat/public-repo',
                'description' => 'A public repository',
                'html_url' => 'https://github.com/octocat/public-repo',
                'language' => 'PHP',
                'topics' => [],
                'stargazers_count' => 3,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
                'private' => false,
                'visibility' => 'public',
            ],
            [
                'id' => 2,
                'name' => 'secret-repo',
                'full_name' => 'octocat/secret-repo',
                'description' => 'A private repository',
                'html_url' => 'https://github.com/octocat/secret-repo',
                'language' => 'PHP',
                'topics' => [],
                'stargazers_count' => 0,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
                'private' => true,
                'visibility' => 'private',
            ],
            [
                'id' => 3,
                'name' => 'internal-repo',
                'full_name' => 'octocat/internal-repo',
                'description' => 'An internal repository',
                'html_url' => 'https://github.com/octocat/internal-repo',
                'language' => 'Go',
                'topics' => [],
                'stargazers_count' => 0,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
                'private' => false,
                'visibility' => 'internal',
            ],
        ], 200),
        'api.github.com/repos/octocat/public-repo/readme' => Http::response('# Public Repo', 200),
    ]);

    $this->artisan('github:sync')->assertExitCode(0);

    expect(Repository::pluck('full_name')->all())->toBe(['octocat/public-repo']);
});

test('github:sync never requests the README of a private repository', function () {
    config(['services.github.username' => 'octocat', 'services.github.token' => 'test-token']);

    Http::fake([
        'api.github.com/users/octocat/repos*' => Http::response([
            [
                'id' => 2,
                'name' => 'secret-repo',
                'full_name' => 'octocat/secret-repo',
                'description' => 'A private repository',
                'html_url' => 'https://github.com/octocat/secret-repo',
                'language' => 'PHP',
                'topics' => [],
                'stargazers_count' => 0,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
                'private' => true,
                'visibility' => 'private',
            ],
        ], 200),
    ]);

    $this->artisan('github:sync')->assertExitCode(0);

    expect(Repository::count())->toBe(0);

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/repos/octocat/secret-repo/readme'));
});

test('github:sync removes repositories that are no longer returned by the API', function () {
    config(['services.github.username' => 'octocat', 'services.github.token' => null]);

    Repository::factory()->create(['github_id' => 1, 'full_name' => 'octocat/still-public']);
    Repository::factory()->create(['github_id' => 99, 'full_name' => 'octocat/went-private']);

    Http::fake([
        'api.github.com/users/octocat/repos*' => Http::response([
            [
                'id' => 1,
                'name' => 'still-public',
                'full_name' => 'octocat/still-public',
                'description' => 'Still public',
                'html_url' => 'https://github.com/octocat/still-public',
                'language' => 'PHP',
                'topics' => [],
                'stargazers_count' => 5,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
                'private' => false,
                'visibility' => 'public',
            ],
        ], 200),
        'api.github.com/repos/octocat/still-public/readme' => Http::response('# Still Public', 200),
    ]);

    $this->artisan('github:sync')->assertExitCode(0);

    expect(Repository::pluck('github_id')->all())->toBe([1]);
});

test('github:sync empties the table when the account has no public repositories left', function () {
    config(['services.github.username' => 'octocat', 'services.github.token' => null]);

    Repository::factory()->create(['github_id' => 1]);

    Http::fake([
        'api.github.com/users/octocat/repos*' => Http::response([], 200),
    ]);

    $this->artisan('github:sync')->assertExitCode(0);

    expect(Repository::count())->toBe(0);
});

test('github:sync excludes the GitHub profile README repository', function () {
    config(['services.github.username' => 'kentaro-nakayama', 'services.github.token' => null]);

    Http::fake([
        'api.github.com/users/kentaro-nakayama/repos*' => Http::response([
            [
                'id' => 1,
                'name' => 'kentaro-nakayama',
                'full_name' => 'kentaro-nakayama/kentaro-nakayama',
                'description' => 'Self introduction',
                'html_url' => 'https://github.com/kentaro-nakayama/kentaro-nakayama',
                'language' => null,
                'topics' => [],
                'stargazers_count' => 0,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
                'private' => false,
                'visibility' => 'public',
            ],
            [
                'id' => 2,
                'name' => 'portfolio-app',
                'full_name' => 'kentaro-nakayama/portfolio-app',
                'description' => 'A real project',
                'html_url' => 'https://github.com/kentaro-nakayama/portfolio-app',
                'language' => 'PHP',
                'topics' => [],
                'stargazers_count' => 1,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
                'private' => false,
                'visibility' => 'public',
            ],
        ], 200),
        'api.github.com/repos/kentaro-nakayama/portfolio-app/readme' => Http::response('# Portfolio App', 200),
    ]);

    $this->artisan('github:sync')->assertExitCode(0);

    expect(Repository::pluck('full_name')->all())->toBe(['kentaro-nakayama/portfolio-app']);
});

test('github:sync removes the profile repository if it was stored by an earlier sync', function () {
    config(['services.github.username' => 'kentaro-nakayama', 'services.github.token' => null]);

    Repository::factory()->create([
        'github_id' => 1,
        'name' => 'kentaro-nakayama',
        'slug' => 'kentaro-nakayama',
        'full_name' => 'kentaro-nakayama/kentaro-nakayama',
    ]);

    Http::fake([
        'api.github.com/users/kentaro-nakayama/repos*' => Http::response([
            [
                'id' => 1,
                'name' => 'kentaro-nakayama',
                'full_name' => 'kentaro-nakayama/kentaro-nakayama',
                'description' => 'Self introduction',
                'html_url' => 'https://github.com/kentaro-nakayama/kentaro-nakayama',
                'language' => null,
                'topics' => [],
                'stargazers_count' => 0,
                'forks_count' => 0,
                'default_branch' => 'main',
                'pushed_at' => '2026-01-01T00:00:00Z',
                'fork' => false,
                'private' => false,
                'visibility' => 'public',
            ],
        ], 200),
    ]);

    $this->artisan('github:sync')->assertExitCode(0);

    expect(Repository::count())->toBe(0);
});
