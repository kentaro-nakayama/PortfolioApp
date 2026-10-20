@extends('layouts.app')

@section('title', 'Projects - ' . config('app.name'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Projects</h1>

    <form method="get" class="mb-8 flex flex-wrap gap-4">
        <select name="language" onchange="this.form.submit()" class="rounded border-gray-300 text-sm">
            <option value="">All languages</option>
            @foreach ($languages as $language)
                <option value="{{ $language }}" @selected($selectedLanguage === $language)>{{ $language }}</option>
            @endforeach
        </select>

        <select name="topic" onchange="this.form.submit()" class="rounded border-gray-300 text-sm">
            <option value="">All topics</option>
            @foreach ($topics as $topic)
                <option value="{{ $topic }}" @selected($selectedTopic === $topic)>{{ $topic }}</option>
            @endforeach
        </select>
    </form>

    @if ($repositories->isEmpty())
        <p class="text-gray-500">No projects found yet. Run <code>php artisan github:sync</code> to fetch them from GitHub.</p>
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        @foreach ($repositories as $repository)
            <a href="{{ route('projects.show', $repository) }}" class="block overflow-hidden rounded-lg border border-gray-200 transition-shadow duration-200 hover:border-indigo-400 hover:shadow-[0_0_12px_rgba(99,102,241,0.35)]">
                @if ($repository->screenshot_url)
                    <img src="{{ $repository->screenshot_url }}" alt="" class="aspect-video w-full object-cover">
                @endif
                <div class="p-5">
                    <h2 class="text-lg font-semibold">{{ $repository->name }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $repository->description }}</p>
                    <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                        @if ($repository->language)
                            <span>{{ $repository->language }}</span>
                        @endif
                        <span>★ {{ $repository->stargazers_count }}</span>
                        <span>⑂ {{ $repository->forks_count }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endsection
