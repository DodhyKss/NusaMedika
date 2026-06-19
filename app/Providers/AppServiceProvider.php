<?php

namespace App\Providers;

use App\Http\View\Composers\InformasiPasienComposer;
use App\Http\View\Composers\SidebarComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        view::composer('layouts.sidebar', SidebarComposer::class);
        view::composer('components.informasi-pasien', InformasiPasienComposer::class);
    }
}
