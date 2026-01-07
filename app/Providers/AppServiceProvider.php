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
        // Garantir que o symlink de storage exista em ambientes locais / docker
        // Sem o link `public/storage` os logos (e outros uploads) não carregam.
        $autoLink = env('CREATE_STORAGE_LINK', false);
        if ($autoLink && $this->app->environment(['local', 'development']) && ! is_link(public_path('storage'))) {
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
            } catch (\Throwable $e) {
                \Log::warning('Falha ao criar storage:link automaticamente: '.$e->getMessage());
            }
        }
    }
}
