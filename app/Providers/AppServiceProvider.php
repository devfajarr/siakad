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
        $this->app->singleton(\App\Services\UserSyncService::class, function ($app) {
            return new \App\Services\UserSyncService();
        });
    }

    public function boot(): void
    {
        \App\Models\Dosen::observe(\App\Observers\DosenObserver::class);
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        \Illuminate\Support\Facades\Gate::define('is-academic-advisor', function (\App\Models\User $user) {
            if (!$user->dosen) {
                return false;
            }
            return \App\Models\PembimbingAkademik::where('id_dosen', $user->dosen->id)
                ->where('id_semester', getActiveSemesterId())
                ->exists();
        });
    }
}
