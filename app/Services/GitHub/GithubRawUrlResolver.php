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
        if (self::isAbsolute($url)) {
            return $url;
        }

        return "https://raw.githubusercontent.com/{$fullName}/{$defaultBranch}/".self::normalizePath($url);
    }

    /**
     * Resolve a (possibly relative) README link to GitHub's rendered file view.
     *
     * A README's links point at files inside the repository (e.g. `docs/requirements.md`).
     * Left relative, the browser resolves them against this site's own URL and they 404,
     * so they are rewritten to `blob` URLs, where GitHub renders the document itself.
     * Raw URLs are deliberately not used here: they would serve Markdown as plain text.
     */
    public static function resolveLink(string $url, string $fullName, string $defaultBranch): string
    {
        // An in-page anchor must keep pointing at the rendered README on this page.
        if (str_starts_with($url, '#') || self::isAbsolute($url)) {
            return $url;
        }

        return "https://github.com/{$fullName}/blob/{$defaultBranch}/".self::normalizePath($url);
    }

    /**
     * Whether the URL already addresses a location on its own, and must be left alone.
     *
     * Besides `http`/`https`/`data`, this covers schemes a README may legitimately use,
     * such as `mailto:` and `tel:`, as well as protocol-relative URLs (`//example.com`).
     */
    private static function isAbsolute(string $url): bool
    {
        return str_starts_with($url, '//')
            || preg_match('/^[a-z][a-z0-9+.-]*:/i', $url) === 1;
    }

    /**
     * Strip the leading `./` or `/` of a repository-relative path so it can be appended
     * to a GitHub base URL without producing a double slash.
     *
     * Only those markers are removed: a leading dot that belongs to the path itself, as in
     * `.github/workflows/tests.yml`, must survive, or the resulting URL points at a file
     * that does not exist.
     */
    private static function normalizePath(string $url): string
    {
        return ltrim(preg_replace('#^(?:\./)+#', '', $url), '/');
    }
}
