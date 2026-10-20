<?php

namespace App\Console\Commands;

use App\Models\Repository;
use App\Services\GitHub\GitHubClient;
use App\Services\GitHub\ReadmeScreenshotExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncGithubRepositories extends Command
{
    protected $signature = 'github:sync';

    protected $description = 'Sync the configured GitHub account\'s public repositories (excluding forks) into the database.';

    public function handle(GitHubClient $client): int
    {
        $repositories = $client->ownedRepositories();

        $this->info(sprintf('Fetched %d public repositories.', count($repositories)));

        $syncedGithubIds = [];

        foreach ($repositories as $data) {
            $syncedGithubIds[] = $data['id'];

            $readme = $client->readme($data['full_name']);
            $screenshotUrl = ReadmeScreenshotExtractor::extract($readme, $data['full_name'], $data['default_branch']);

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
                    'screenshot_url' => $screenshotUrl,
                    'pushed_at' => $data['pushed_at'],
                    'synced_at' => now(),
                ]
            );
        }

        $removed = Repository::query()
            ->whereNotIn('github_id', $syncedGithubIds)
            ->delete();

        if ($removed > 0) {
            $this->warn(sprintf(
                'Removed %d repositories that are no longer listed (made private, deleted, forked, or excluded by configuration).',
                $removed,
            ));
        }

        $this->info('Sync complete.');

        return self::SUCCESS;
    }
}
