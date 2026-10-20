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
