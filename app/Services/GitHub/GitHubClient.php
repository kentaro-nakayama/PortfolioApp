<?php

namespace App\Services\GitHub;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GitHubClient
{
    private const BASE_URL = 'https://api.github.com';

    public function __construct(
        private readonly string $username,
        private readonly ?string $token = null,
    ) {}

    /**
     * Fetch all public, non-fork repositories owned by the configured user,
     * excluding the GitHub profile README repository.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ownedRepositories(): array
    {
        $repositories = [];
        $page = 1;

        do {
            $response = $this->request()
                ->get(self::BASE_URL."/users/{$this->username}/repos", [
                    'type' => 'owner',
                    'sort' => 'updated',
                    'per_page' => 100,
                    'page' => $page,
                ])
                ->throw();

            $batch = $response->json();
            $repositories = [...$repositories, ...$batch];
            $page++;
        } while (count($batch) === 100);

        return array_values(array_filter(
            $repositories,
            fn (array $repository) => $this->isPublicSource($repository)
                && ! $this->isProfileRepository($repository),
        ));
    }

    /**
     * Whether the payload is the user's GitHub profile README repository,
     * which by convention is named after the account itself and only holds
     * the self-introduction rendered on the profile page.
     *
     * @param  array<string, mixed>  $repository
     */
    private function isProfileRepository(array $repository): bool
    {
        return strcasecmp($repository['name'] ?? '', $this->username) === 0;
    }

    /**
     * Whether a repository payload is a public repository the user owns outright.
     *
     * The /users/{username}/repos endpoint only returns public repositories, but the
     * check is made explicit here so private or internal repositories can never reach
     * the database if the endpoint or its parameters change.
     *
     * @param  array<string, mixed>  $repository
     */
    private function isPublicSource(array $repository): bool
    {
        if (($repository['fork'] ?? false) !== false) {
            return false;
        }

        if (($repository['private'] ?? false) !== false) {
            return false;
        }

        return ($repository['visibility'] ?? 'public') === 'public';
    }

    /**
     * Fetch the raw README content for a repository, or null if it has none.
     */
    public function readme(string $fullName): ?string
    {
        $response = $this->request()
            ->withHeaders(['Accept' => 'application/vnd.github.raw+json'])
            ->get(self::BASE_URL."/repos/{$fullName}/readme");

        if ($response->notFound()) {
            return null;
        }

        return $response->throw()->body();
    }

    private function request(): PendingRequest
    {
        $request = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ]);

        return $this->token
            ? $request->withToken($this->token)
            : $request;
    }
}
