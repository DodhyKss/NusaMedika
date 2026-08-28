<?php

namespace App\Providers;

use App\Http\Controllers\SubMenu\SubMenuViewController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SubMenuRouteServiceProvider extends ServiceProvider
{
    public const PATH_CACHE_KEY = 'sub_menu_route_paths';

    public function boot(): void
    {
        $this->app->booted(function () {
            $existing = collect(Route::getRoutes()->getRoutes())
                ->map(fn ($route) => trim($route->uri(), '/'))
                ->filter()
                ->unique()
                ->values();

            foreach ($this->subMenuPaths() as $path) {
                if ($existing->contains($path)) {
                    continue;
                }

                Route::get('/'.$path, SubMenuViewController::class)
                    ->middleware('web', 'auth')
                    ->name('modul_view.'.str_replace('/', '.', $path));
            }
        });
    }

    public static function flushPathCache(): void
    {
        Cache::forget(self::PATH_CACHE_KEY);
    }

    private function subMenuPaths(): array
    {
        return Cache::remember(self::PATH_CACHE_KEY, now()->addHours(24), function () {
            return DB::table('sub_menu')
                ->where('file_sub_menu', '!=', '#')
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->distinct()
                ->pluck('file_sub_menu')
                ->filter(fn ($path) => is_string($path) && preg_match('#^[a-zA-Z0-9_\-]+(?:/[a-zA-Z0-9_\-]+)*$#', $path))
                ->values()
                ->all();
        });
    }
}
