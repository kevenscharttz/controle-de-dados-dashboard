<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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

        // Fallback: garantir que o usuário admin tenha role super_admin em produção
        // Útil em plataformas sem shell (Render free), idempotente e leve.
        if ($this->app->environment('production')) {
            // Forçar HTTPS em produção para evitar mixed content (Render usa HTTPS)
            try {
                URL::forceScheme('https');
            } catch (\Throwable $e) {
                \Log::warning('Falha ao forcar esquema https: '.$e->getMessage());
            }

            try {
                $email = env('DOCKER_ADMIN_EMAIL') ?? env('ADMIN_EMAIL', 'admin@example.com');
                /** @var \App\Models\User|null $admin */
                $admin = \App\Models\User::where('email', $email)->first();
                if ($admin && method_exists($admin, 'hasRole') && ! ($admin->hasRole('super_admin') || $admin->hasRole('super-admin'))) {
                    // Cria a role se não existir e atribui
                    $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
                    $admin->assignRole($role);
                }
            } catch (\Throwable $e) {
                // Silencioso para não quebrar boot; loga se necessário
                \Log::warning('Falha no fallback de super_admin: '.$e->getMessage());
            }
        }
    }
}
