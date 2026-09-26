<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repositories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('github_id')->unique();
            $table->string('name');
            $table->string('full_name');
            $table->text('description')->nullable();
            $table->string('html_url');
            $table->string('language')->nullable();
            $table->json('topics')->nullable();
            $table->unsignedInteger('stargazers_count')->default(0);
            $table->unsignedInteger('forks_count')->default(0);
            $table->string('default_branch')->default('main');
            $table->longText('readme')->nullable();
            $table->timestamp('pushed_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->string('slug')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
