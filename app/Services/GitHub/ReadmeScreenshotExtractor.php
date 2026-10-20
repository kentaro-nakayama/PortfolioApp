<?php

namespace App\Services\GitHub;

class ReadmeScreenshotExtractor
{
    /**
     * Hosts that serve badges (build status, license, npm version, ...) rather than screenshots.
     */
    private const BADGE_HOSTS = [
        'shields.io',
        'badge.fury.io',
        'badgen.net',
        'travis-ci.com',
        'travis-ci.org',
        'coveralls.io',
        'codecov.io',
        'snyk.io',
        'opencollective.com',
        'david-dm.org',
        'deepsource.io',
        'sonarcloud.io',
        'circleci.com',
        'codeclimate.com',
        'forthebadge.com',
    ];

    /**
     * Find the first non-badge image referenced in a README and resolve it to an absolute URL.
     *
     * Looks at both Markdown image syntax (`![alt](url)`) and raw `<img src="...">` tags, in the
     * order they appear in the document, since READMEs commonly wrap screenshots in HTML for centering.
     */
    public static function extract(?string $markdown, string $fullName, string $defaultBranch): ?string
    {
        if (! $markdown) {
            return null;
        }

        $pattern = '/!\[[^\]]*\]\(\s*<?(?<md_url>[^)\s>]+)[^)]*\)'
            .'|<img\b[^>]*\bsrc\s*=\s*["\'](?<html_url>[^"\']+)["\'][^>]*>/i';

        preg_match_all($pattern, $markdown, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $url = $match['md_url'] !== '' ? $match['md_url'] : $match['html_url'];

            if ($url === '' || self::isBadge($url)) {
                continue;
            }

            return GithubRawUrlResolver::resolve($url, $fullName, $defaultBranch);
        }

        return null;
    }

    private static function isBadge(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;

        if (str_ends_with(strtolower($path), '.svg')) {
            return true;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null) {
            return false;
        }

        foreach (self::BADGE_HOSTS as $badgeHost) {
            if (str_contains($host, $badgeHost)) {
                return true;
            }
        }

        return false;
    }
}
