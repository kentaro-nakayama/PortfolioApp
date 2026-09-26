<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name'))</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
        <div class="mx-auto flex min-h-screen max-w-4xl flex-col px-6">
            <header class="flex items-center justify-between py-8">
                <a href="{{ route('projects.index') }}" class="text-lg font-semibold">
                    {{ config('app.name') }}
                </a>
                <nav class="flex gap-6 text-sm">
                    <a href="{{ route('projects.index') }}" class="hover:text-indigo-600">Projects</a>
                    <a href="{{ route('about') }}" class="hover:text-indigo-600">About</a>
                </nav>
            </header>

            <main class="flex-1 pb-16">
                @yield('content')
            </main>

            <footer class="border-t border-gray-200 py-6 text-center text-sm text-gray-500">
                &copy; {{ now()->year }} {{ config('app.name') }}
            </footer>
        </div>
    </body>
</html>
