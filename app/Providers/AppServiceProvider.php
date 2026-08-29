<?php

namespace App\Providers;

use App\Http\View\Composers\InformasiPasienComposer;
use App\Http\View\Composers\SidebarComposer;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.sidebar', SidebarComposer::class);
        View::composer('components.informasi-pasien', InformasiPasienComposer::class);
    }
}
