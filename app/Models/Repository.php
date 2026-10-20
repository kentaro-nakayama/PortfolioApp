<?php

namespace App\Models;

use Database\Factories\RepositoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    /** @use HasFactory<RepositoryFactory> */
    use HasFactory;

    protected $fillable = [
        'github_id',
        'name',
        'slug',
        'full_name',
        'description',
        'html_url',
        'language',
        'topics',
        'stargazers_count',
        'forks_count',
        'default_branch',
        'readme',
        'screenshot_url',
        'pushed_at',
        'synced_at',
    ];

    protected $casts = [
        'topics' => 'array',
        'pushed_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
