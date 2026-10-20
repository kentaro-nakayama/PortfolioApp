<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredRepositories = Repository::query()
            ->orderByDesc('pushed_at')
            ->limit(3)
            ->get();

        return view('home', [
            'featuredRepositories' => $featuredRepositories,
        ]);
    }
}
