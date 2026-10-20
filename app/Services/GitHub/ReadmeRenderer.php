<?php

namespace App\Services\GitHub;

use Illuminate\Support\Str;

class ReadmeRenderer
{
    /**
     * Render a README's raw Markdown to HTML, rewriting the relative URLs it contains so they
     * still work outside of GitHub's own UI: images (e.g. `docs/screenshots/foo.png`) point at
     * raw.githubusercontent.com, and links (e.g. `docs/requirements.md`) at GitHub's file view.
     * Left as-is, both would resolve against this site's URL and 404.
     */
    public static function toHtml(?string $markdown, string $fullName, string $defaultBranch): string
    {
        if (! $markdown) {
            return '';
        }

        $html = Str::markdown($markdown);

        $html = preg_replace_callback(
            '/(<img\b[^>]*?\bsrc=["\'])([^"\']+)(["\'][^>]*>)/i',
            fn (array $matches) => $matches[1].GithubRawUrlResolver::resolve($matches[2], $fullName, $defaultBranch).$matches[3],
            $html
        );

        return preg_replace_callback(
            '/(<a\b[^>]*?\bhref=["\'])([^"\']+)(["\'][^>]*>)/i',
            fn (array $matches) => $matches[1].GithubRawUrlResolver::resolveLink($matches[2], $fullName, $defaultBranch).$matches[3],
            $html
        );
    }
}
