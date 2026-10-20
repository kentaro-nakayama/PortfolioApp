<?php

namespace App\Services\GitHub;

class GithubRawUrlResolver
{
    /**
     * Resolve a (possibly relative) README asset URL to an absolute one.
     *
     * Relative paths (e.g. `docs/screenshot.png`) are resolved against the repository's
     * raw content URL on the given branch, since that's where GitHub actually serves them from.
     */
    public static function resolve(string $url, string $fullName, string $defaultBranch): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'data:')) {
            return $url;
        }

        $path = ltrim($url, './');

        return "https://raw.githubusercontent.com/{$fullName}/{$defaultBranch}/{$path}";
    }
}
