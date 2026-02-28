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
        \App\Models\Kaprodi::observe(\App\Observers\KaprodiObserver::class);
        \App\Models\Mahasiswa::observe(\App\Observers\MahasiswaObserver::class);
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        \Illuminate\Support\Facades\Gate::define('is-academic-advisor', function (\App\Models\User $user) {
            if (!$user->dosen) {
                return false;
            }
            return \App\Models\PembimbingAkademik::where('id_dosen', $user->dosen->id)
                ->where('id_semester', getActiveSemesterId())
                ->exists();
        });

        \Illuminate\Support\Facades\Gate::define('is-kaprodi', function (\App\Models\User $user) {
            if (!$user->dosen) {
                return false;
            }
            return \App\Models\Kaprodi::where('dosen_id', $user->dosen->id)->exists();
        });
    }
}
