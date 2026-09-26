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

test('the about page is reachable', function () {
    $response = $this->get(route('about'));

    $response->assertOk();
});
