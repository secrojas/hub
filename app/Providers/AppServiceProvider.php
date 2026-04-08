<?php

namespace App\Providers;

use App\Contracts\Repositories\NoteFolderRepositoryInterface;
use App\Contracts\Repositories\NoteRepositoryInterface;
use App\Repositories\NoteFolderRepository;
use App\Repositories\NoteRepository;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NoteRepositoryInterface::class, NoteRepository::class);
        $this->app->bind(NoteFolderRepositoryInterface::class, NoteFolderRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
