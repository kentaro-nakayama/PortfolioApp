@extends('layouts.app')

@section('title', $repository->name . ' - ' . config('app.name'))

@section('content')
    <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600">&larr; Back to projects</a>

    <h1 class="mt-4 text-2xl font-bold">{{ $repository->name }}</h1>
    <p class="mt-2 text-gray-600">{{ $repository->description }}</p>

    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-gray-500">
        @if ($repository->language)
            <span>{{ $repository->language }}</span>
        @endif
        <span>★ {{ $repository->stargazers_count }}</span>
        <span>⑂ {{ $repository->forks_count }}</span>
        <a href="{{ $repository->html_url }}" target="_blank" rel="noopener" class="text-indigo-600">
            View on GitHub &rarr;
        </a>
    </div>

    @if (! empty($repository->topics))
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($repository->topics as $topic)
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs text-indigo-700">{{ $topic }}</span>
            @endforeach
        </div>
    @endif

    <article class="prose prose-sm mt-10 max-w-none">
        @if ($repository->readme)
            {!! \Illuminate\Support\Str::markdown($repository->readme) !!}
        @else
            <p class="text-gray-500">No README available for this project.</p>
        @endif
    </article>
@endsection
