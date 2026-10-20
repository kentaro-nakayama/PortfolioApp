<?php

use App\Services\GitHub\ReadmeScreenshotExtractor;

test('it resolves a relative markdown image to a raw githubusercontent url', function () {
    $markdown = "# My App\n\n![screenshot](docs/screenshot.png)\n\nSome description.";

    $url = ReadmeScreenshotExtractor::extract($markdown, 'octocat/my-app', 'main');

    expect($url)->toBe('https://raw.githubusercontent.com/octocat/my-app/main/docs/screenshot.png');
});

test('it keeps an absolute image url as-is', function () {
    $markdown = '![demo](https://user-images.githubusercontent.com/1/demo.png)';

    $url = ReadmeScreenshotExtractor::extract($markdown, 'octocat/my-app', 'main');

    expect($url)->toBe('https://user-images.githubusercontent.com/1/demo.png');
});

test('it extracts an image from a raw html img tag used to center a screenshot', function () {
    $markdown = '<p align="center"><img src="assets/preview.gif" width="600"></p>';

    $url = ReadmeScreenshotExtractor::extract($markdown, 'octocat/my-app', 'main');

    expect($url)->toBe('https://raw.githubusercontent.com/octocat/my-app/main/assets/preview.gif');
});

test('it skips shields.io and other badge images and picks the first real screenshot', function () {
    $markdown = <<<'MD'
    ![Build Status](https://img.shields.io/github/actions/workflow/status/octocat/my-app/tests.yml)
    ![License](https://img.shields.io/badge/license-MIT-blue.svg)

    ![screenshot](docs/screenshot.png)
    MD;

    $url = ReadmeScreenshotExtractor::extract($markdown, 'octocat/my-app', 'main');

    expect($url)->toBe('https://raw.githubusercontent.com/octocat/my-app/main/docs/screenshot.png');
});

test('it skips svg images since they are almost always badges or logos', function () {
    $markdown = '![logo](docs/logo.svg)';

    $url = ReadmeScreenshotExtractor::extract($markdown, 'octocat/my-app', 'main');

    expect($url)->toBeNull();
});

test('it returns null when the readme has no images', function () {
    $markdown = "# My App\n\nJust some text, no images here.";

    $url = ReadmeScreenshotExtractor::extract($markdown, 'octocat/my-app', 'main');

    expect($url)->toBeNull();
});

test('it returns null when the readme is null', function () {
    $url = ReadmeScreenshotExtractor::extract(null, 'octocat/my-app', 'main');

    expect($url)->toBeNull();
});
