<?php

namespace App\Console\Commands;

use App\Models\Repository;
use App\Services\GitHub\GitHubClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncGithubRepositories extends Command
{
    protected $signature = 'github:sync';

    protected $description = 'Sync the configured GitHub account\'s public repositories (excluding forks) into the database.';

    public function handle(GitHubClient $client): int
    {
        $repositories = $client->ownedRepositories();

        $this->info(sprintf('Fetched %d repositories.', count($repositories)));

        foreach ($repositories as $data) {
            $readme = $client->readme($data['full_name']);

            Repository::updateOrCreate(
                ['github_id' => $data['id']],
                [
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'full_name' => $data['full_name'],
                    'description' => $data['description'],
                    'html_url' => $data['html_url'],
                    'language' => $data['language'],
                    'topics' => $data['topics'] ?? [],
                    'stargazers_count' => $data['stargazers_count'],
                    'forks_count' => $data['forks_count'],
                    'default_branch' => $data['default_branch'],
                    'readme' => $readme,
                    'pushed_at' => $data['pushed_at'],
                    'synced_at' => now(),
                ]
            );
        }

        $this->info('Sync complete.');

        return self::SUCCESS;
    }
}
