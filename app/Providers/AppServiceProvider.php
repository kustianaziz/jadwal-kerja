<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Database\Eloquent\Relations\Relation::enforceMorphMap([
            'project' => \App\Models\Project::class,
            'phase' => \App\Models\Phase::class,
            'task' => \App\Models\Task::class,
            'journal' => \App\Models\JournalEntry::class,
        ]);
    }
}
