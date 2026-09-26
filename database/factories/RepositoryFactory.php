<?php

namespace Database\Factories;

use App\Models\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Repository>
 */
class RepositoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->slug(2);

        return [
            'github_id' => $this->faker->unique()->numberBetween(1, 1_000_000),
            'name' => $name,
            'slug' => Str::slug($name),
            'full_name' => "octocat/{$name}",
            'description' => $this->faker->sentence(),
            'html_url' => "https://github.com/octocat/{$name}",
            'language' => $this->faker->randomElement(['PHP', 'JavaScript', 'Go', null]),
            'topics' => $this->faker->randomElements(['laravel', 'cli', 'api', 'testing'], 2),
            'stargazers_count' => $this->faker->numberBetween(0, 500),
            'forks_count' => $this->faker->numberBetween(0, 100),
            'default_branch' => 'main',
            'readme' => "# {$name}\n\nSample README content.",
            'pushed_at' => $this->faker->dateTimeBetween('-1 year'),
            'synced_at' => now(),
        ];
    }
}
