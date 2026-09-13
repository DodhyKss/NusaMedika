<?php

namespace App\Providers;

use App\Console\Commands\Make\ModelMakeCommand;
use App\Http\View\Composers\InformasiPasienComposer;
use App\Http\View\Composers\SidebarComposer;
use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ganti make:model bawaan agar nama tabel dibuat singular dan tidak
        // memecah akronim (Barang => barang, KategoriSPM => kategori_spm).
        Artisan::starting(function (Artisan $artisan) {
            $this->app->make(Kernel::class)->addCommands([
                ModelMakeCommand::class,
            ]);
        });
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
