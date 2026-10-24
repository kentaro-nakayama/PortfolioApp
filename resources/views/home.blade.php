@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    <section class="py-12">
        <div class="flex items-start justify-between gap-8">
            <div>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">中山 健太郎</h1>
                <p class="mt-4 max-w-2xl text-gray-600">
                    鳥取県在住，Webエンジニアを目指して学習中です．
                    PHP / Laravel / TypeScriptを中心に，日々の暮らしを便利にするWebアプリをつくっています．
                </p>

                <div class="mt-6 flex gap-4">
                    <a href="{{ route('projects.index') }}" class="rounded bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition-colors duration-150 hover:bg-indigo-500">
                        Projectsを見る
                    </a>
                    <a href="{{ route('about') }}" class="rounded border border-gray-300 px-5 py-2.5 text-sm font-medium transition-colors duration-150 hover:border-indigo-400">
                        Aboutを見る
                    </a>
                </div>
            </div>

            @php($profileImagePath = 'images/profile.jpg')
            @if (file_exists(public_path($profileImagePath)))
                <img
                    src="{{ asset($profileImagePath) }}"
                    alt="{{ config('app.name') }}"
                    class="size-32 shrink-0 rounded-full object-cover ring-1 ring-gray-200 sm:size-40"
                >
            @else
                <div class="flex size-32 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs text-gray-400 ring-1 ring-gray-200 sm:size-40">
                    No Image
                </div>
            @endif
        </div>
    </section>

    <section class="border-t border-gray-200 py-12">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Recent Projects</h2>
            <a href="{{ route('projects.index') }}" class="group text-sm text-indigo-600 hover:underline">
                すべて見る <span class="inline-block transition-transform duration-150 group-hover:translate-x-1">&rarr;</span>
            </a>
        </div>

        @if ($featuredRepositories->isEmpty())
            <p class="mt-6 text-sm text-gray-500">
                まだプロジェクトが同期されていません。<code>php artisan github:sync</code> を実行してください。
            </p>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-3">
                @foreach ($featuredRepositories as $repository)
                    <a href="{{ route('projects.show', $repository) }}" class="block overflow-hidden rounded-lg border border-gray-200 transition-shadow duration-200 hover:border-indigo-400 hover:shadow-[0_0_8px_rgba(99,102,241,0.35)]">
                        @if ($repository->screenshot_url)
                            <img src="{{ $repository->screenshot_url }}" alt="" class="aspect-video w-full object-cover">
                        @else
                            <div class="flex aspect-video w-full items-center justify-center bg-gray-100 text-xs text-gray-400">
                                No Image
                            </div>
                        @endif
                        <div class="p-5">
                            <h3 class="font-semibold">{{ $repository->name }}</h3>
                            <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $repository->description }}</p>
                            <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                                @if ($repository->language)
                                    <span>{{ $repository->language }}</span>
                                @endif
                                <span>★ {{ $repository->stargazers_count }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
