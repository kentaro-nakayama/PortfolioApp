<?php

use App\Models\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the home page is reachable', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('the home page shows the most recently pushed repositories, limited to three', function () {
    Repository::factory()->create(['name' => 'oldest', 'pushed_at' => now()->subDays(3)]);
    Repository::factory()->create(['name' => 'middle', 'pushed_at' => now()->subDays(2)]);
    Repository::factory()->create(['name' => 'newest', 'pushed_at' => now()->subDay()]);
    Repository::factory()->create(['name' => 'excluded', 'pushed_at' => now()->subDays(4)]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSeeInOrder(['newest', 'middle', 'oldest']);
    $response->assertDontSee('excluded');
});

test('the home page displays a repository screenshot when one was extracted', function () {
    Repository::factory()->create([
        'name' => 'screenshot-project',
        'screenshot_url' => 'https://raw.githubusercontent.com/octocat/screenshot-project/main/docs/screenshot.png',
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('https://raw.githubusercontent.com/octocat/screenshot-project/main/docs/screenshot.png', false);
});
