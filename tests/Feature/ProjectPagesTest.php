<?php

use App\Models\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the project index page lists synced repositories', function () {
    Repository::factory()->create(['name' => 'my-cool-project', 'language' => 'PHP']);
    Repository::factory()->create(['name' => 'another-project', 'language' => 'Go']);

    $response = $this->get(route('projects.index'));

    $response->assertOk();
    $response->assertSee('my-cool-project');
    $response->assertSee('another-project');
});

test('the project index page can be filtered by language', function () {
    Repository::factory()->create(['name' => 'php-project', 'language' => 'PHP']);
    Repository::factory()->create(['name' => 'go-project', 'language' => 'Go']);

    $response = $this->get(route('projects.index', ['language' => 'PHP']));

    $response->assertOk();
    $response->assertSee('php-project');
    $response->assertDontSee('go-project');
});

test('the project index page can be filtered by topic', function () {
    Repository::factory()->create(['name' => 'laravel-project', 'topics' => ['laravel', 'php']]);
    Repository::factory()->create(['name' => 'cli-project', 'topics' => ['cli']]);

    $response = $this->get(route('projects.index', ['topic' => 'laravel']));

    $response->assertOk();
    $response->assertSee('laravel-project');
    $response->assertDontSee('cli-project');
});

test('the project index page shows a hint when there are no repositories yet', function () {
    $response = $this->get(route('projects.index'));

    $response->assertOk();
    $response->assertSee('github:sync');
});

test('the project show page renders the readme as markdown', function () {
    $repository = Repository::factory()->create([
        'name' => 'markdown-project',
        'readme' => "# Markdown Project\n\nThis is **bold** text.",
    ]);

    $response = $this->get(route('projects.show', $repository));

    $response->assertOk();
    $response->assertSee('Markdown Project');
    $response->assertSee('<strong>bold</strong>', false);
});

test('the project index page displays a repository screenshot when one was extracted', function () {
    Repository::factory()->create([
        'name' => 'screenshot-project',
        'screenshot_url' => 'https://raw.githubusercontent.com/octocat/screenshot-project/main/docs/screenshot.png',
    ]);

    $response = $this->get(route('projects.index'));

    $response->assertOk();
    $response->assertSee('https://raw.githubusercontent.com/octocat/screenshot-project/main/docs/screenshot.png', false);
});

test('the project show page displays topic badges', function () {
    $repository = Repository::factory()->create([
        'name' => 'badge-project',
        'topics' => ['laravel', 'testing'],
    ]);

    $response = $this->get(route('projects.show', $repository));

    $response->assertOk();
    $response->assertSee('laravel');
    $response->assertSee('testing');
});

test('the project show page resolves relative readme images to absolute github urls', function () {
    $repository = Repository::factory()->create([
        'name' => 'relative-image-project',
        'full_name' => 'octocat/relative-image-project',
        'default_branch' => 'main',
        'readme' => "# Relative Image Project\n\n![Home screen](docs/screenshots/home.png)",
    ]);

    $response = $this->get(route('projects.show', $repository));

    $response->assertOk();
    $response->assertSee(
        'src="https://raw.githubusercontent.com/octocat/relative-image-project/main/docs/screenshots/home.png"',
        false
    );
});

test('the project show page displays the repository screenshot when one was extracted', function () {
    $repository = Repository::factory()->create([
        'name' => 'screenshot-project',
        'screenshot_url' => 'https://raw.githubusercontent.com/octocat/screenshot-project/main/docs/screenshot.png',
    ]);

    $response = $this->get(route('projects.show', $repository));

    $response->assertOk();
    $response->assertSee('https://raw.githubusercontent.com/octocat/screenshot-project/main/docs/screenshot.png', false);
});

test('the project show page shows a fallback message when there is no readme', function () {
    $repository = Repository::factory()->create([
        'name' => 'no-readme-project',
        'readme' => null,
    ]);

    $response = $this->get(route('projects.show', $repository));

    $response->assertOk();
    $response->assertSee('No README available for this project.');
});

test('the about page is reachable', function () {
    $response = $this->get(route('about'));

    $response->assertOk();
});
