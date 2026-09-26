@extends('layouts.app')

@section('title', 'About - ' . config('app.name'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold">About</h1>

    <div class="prose prose-sm max-w-none">
        <p>
            自己紹介文をここに記載してください。
        </p>
    </div>
@endsection
