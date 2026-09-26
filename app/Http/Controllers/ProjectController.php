<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $language = $request->string('language')->trim()->value() ?: null;
        $topic = $request->string('topic')->trim()->value() ?: null;

        $repositories = Repository::query()
            ->when($language, fn ($query) => $query->where('language', $language))
            ->when($topic, fn ($query) => $query->whereJsonContains('topics', $topic))
            ->orderByDesc('pushed_at')
            ->get();

        $languages = Repository::query()
            ->whereNotNull('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');

        $topics = Repository::query()
            ->get()
            ->pluck('topics')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('projects.index', [
            'repositories' => $repositories,
            'languages' => $languages,
            'topics' => $topics,
            'selectedLanguage' => $language,
            'selectedTopic' => $topic,
        ]);
    }

    public function show(Repository $repository): View
    {
        return view('projects.show', [
            'repository' => $repository,
        ]);
    }
}
