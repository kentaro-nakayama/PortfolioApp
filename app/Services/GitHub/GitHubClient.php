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
     * Fetch all non-fork repositories owned by the configured user.
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
            fn (array $repository) => $repository['fork'] === false,
        ));
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
