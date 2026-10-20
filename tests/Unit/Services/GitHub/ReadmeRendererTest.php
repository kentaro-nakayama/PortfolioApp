<?php

use App\Services\GitHub\ReadmeRenderer;

test('it rewrites a relative markdown image src to an absolute raw githubusercontent url', function () {
    $html = ReadmeRenderer::toHtml('![Home screen](docs/screenshots/home.png)', 'octocat/my-app', 'main');

    expect($html)->toContain('src="https://raw.githubusercontent.com/octocat/my-app/main/docs/screenshots/home.png"');
});

test('it leaves absolute image urls untouched', function () {
    $html = ReadmeRenderer::toHtml('![Demo](https://user-images.githubusercontent.com/1/demo.png)', 'octocat/my-app', 'main');

    expect($html)->toContain('src="https://user-images.githubusercontent.com/1/demo.png"');
});

test('it rewrites relative src on raw html img tags used to center a screenshot', function () {
    $html = ReadmeRenderer::toHtml('<p align="center"><img src="docs/screenshots/list.png" width="600"></p>', 'octocat/my-app', 'main');

    expect($html)->toContain('src="https://raw.githubusercontent.com/octocat/my-app/main/docs/screenshots/list.png"');
});

test('it resolves every image in a readme with multiple screenshots', function () {
    $markdown = "![First](docs/screenshots/first.png)\n\n![Second](docs/screenshots/second.png)";

    $html = ReadmeRenderer::toHtml($markdown, 'octocat/my-app', 'main');

    expect($html)
        ->toContain('src="https://raw.githubusercontent.com/octocat/my-app/main/docs/screenshots/first.png"')
        ->toContain('src="https://raw.githubusercontent.com/octocat/my-app/main/docs/screenshots/second.png"');
});

test('it returns an empty string when the readme is null', function () {
    expect(ReadmeRenderer::toHtml(null, 'octocat/my-app', 'main'))->toBe('');
});

test('it rewrites a relative markdown link to the file view on github', function () {
    $html = ReadmeRenderer::toHtml('[要件定義書](docs/requirements.md)', 'octocat/my-app', 'main');

    expect($html)->toContain('href="https://github.com/octocat/my-app/blob/main/docs/requirements.md"');
});

test('it keeps the fragment when rewriting a relative link', function () {
    $html = ReadmeRenderer::toHtml('[テーブル定義](docs/database-design.md#tables)', 'octocat/my-app', 'main');

    expect($html)->toContain('href="https://github.com/octocat/my-app/blob/main/docs/database-design.md#tables"');
});

test('it leaves absolute links untouched', function () {
    $html = ReadmeRenderer::toHtml('[Laravel](https://laravel.com/docs)', 'octocat/my-app', 'main');

    expect($html)->toContain('href="https://laravel.com/docs"');
});

test('it leaves in-page anchor links untouched', function () {
    $html = ReadmeRenderer::toHtml("## Usage\n\n[Jump to usage](#usage)", 'octocat/my-app', 'main');

    expect($html)->toContain('href="#usage"');
});

test('it leaves mailto links untouched', function () {
    $html = ReadmeRenderer::toHtml('[連絡先](mailto:someone@example.com)', 'octocat/my-app', 'main');

    expect($html)->toContain('href="mailto:someone@example.com"');
});

test('it keeps the leading dot of a dotfile directory when rewriting a link', function () {
    $html = ReadmeRenderer::toHtml('[CI](.github/workflows/tests.yml)', 'octocat/my-app', 'main');

    expect($html)->toContain('href="https://github.com/octocat/my-app/blob/main/.github/workflows/tests.yml"');
});

test('it strips only the leading current-directory marker of a relative image path', function () {
    $html = ReadmeRenderer::toHtml('![Home](./docs/home.png)', 'octocat/my-app', 'main');

    expect($html)->toContain('src="https://raw.githubusercontent.com/octocat/my-app/main/docs/home.png"');
});

test('it keeps the leading dot of a dotfile directory when rewriting an image', function () {
    $html = ReadmeRenderer::toHtml('![Badge](.github/assets/badge.png)', 'octocat/my-app', 'main');

    expect($html)->toContain('src="https://raw.githubusercontent.com/octocat/my-app/main/.github/assets/badge.png"');
});
