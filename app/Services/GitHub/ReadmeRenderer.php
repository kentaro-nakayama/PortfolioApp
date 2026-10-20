<?php

namespace App\Services\GitHub;

use Illuminate\Support\Str;

class ReadmeRenderer
{
    /**
     * Render a README's raw Markdown to HTML, rewriting relative image URLs (e.g. `docs/screenshots/foo.png`)
     * to absolute raw.githubusercontent.com URLs so they actually load outside of GitHub's own UI.
     */
    public static function toHtml(?string $markdown, string $fullName, string $defaultBranch): string
    {
        if (! $markdown) {
            return '';
        }

        $html = Str::markdown($markdown);

        return preg_replace_callback(
            '/(<img\b[^>]*?\bsrc=["\'])([^"\']+)(["\'][^>]*>)/i',
            fn (array $matches) => $matches[1].GithubRawUrlResolver::resolve($matches[2], $fullName, $defaultBranch).$matches[3],
            $html
        );
    }
}
