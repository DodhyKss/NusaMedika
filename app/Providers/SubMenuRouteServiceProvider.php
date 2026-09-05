<?php

namespace App\Providers;

use App\Http\Controllers\SubMenu\SubMenuViewController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SubMenuRouteServiceProvider extends ServiceProvider
{
    public const PATH_CACHE_KEY = 'sub_menu_route_records';

    public const LEGACY_PATH_CACHE_KEY = 'sub_menu_route_paths';

    /**
     * Derive URI / nama route / controller / param dari file_sub_menu (path view).
     *
     * file_sub_menu kini menyimpan path view lengkap BESERTA nama file blade (tanpa
     * ekstensi), misal `Registrasi/Pasien/DaftarPasien/daftar_pasien`. Segmen
     * terakhir = basename blade file (= URI), sedangkan folder di depannya
     * (`Registrasi/Pasien/DaftarPasien`) dipakai untuk namespace controller dan
     * path view. Dengan begini URL & route selalu identik dengan nama file blade,
     * memudahkan debugging.
     *
     * @return array{uri: string, route_name: string, controller: ?string, resource: bool, param: string}
     */
    public static function derive(string $fileSubMenu): array
    {
        $segments = array_values(array_filter(explode('/', trim($fileSubMenu, '/'))));

        if ($segments === []) {
            return ['uri' => '', 'route_name' => '', 'controller' => null, 'resource' => false, 'param' => 'id'];
        }

        $uri = (string) end($segments);

        $dirSegments = $segments;
        array_pop($dirSegments);
        $folderLeaf = $dirSegments !== [] ? (string) end($dirSegments) : '';

        $controller = null;
        if ($dirSegments !== [] && $folderLeaf !== '') {
            $namespace = implode('\\', $dirSegments);
            $controller = 'App\\Http\\Controllers\\'.$namespace.'\\'.$folderLeaf.'Controller';
        }

        $isAdmin = $dirSegments !== [] && str_starts_with((string) $dirSegments[0], 'Administrator');
        $routeName = $isAdmin ? 'admin.'.$uri : $uri;
        $controllerExists = $controller !== null && class_exists($controller);

        return [
            'uri' => $uri,
            'route_name' => $routeName,
            'controller' => $controllerExists ? $controller : null,
            'resource' => $controllerExists,
            'param' => $uri,
        ];
    }

    /**
     * URL yang bisa dipakai sidebar untuk membuka sub-menu.
     */
    public static function url(string $fileSubMenu): string
    {
        if ($fileSubMenu === '#' || trim($fileSubMenu, '/') === '') {
            return '#';
        }

        $info = self::derive($fileSubMenu);

        if ($info['resource'] && method_exists($info['controller'], 'index')) {
            try {
                return route($info['route_name'].'.index');
            } catch (\Throwable $e) {
                // route belum terdaftar -> fallback ke uri
            }
        }

        if ($info['uri'] !== '') {
            return url($info['uri']);
        }

        return url($fileSubMenu);
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            $existing = collect(Route::getRoutes()->getRoutes())
                ->map(fn ($route) => trim($route->uri(), '/'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            foreach ($this->subMenus() as $subMenu) {
                $path = (string) $subMenu->file_sub_menu;

                if ($path === '#' || trim($path, '/') === '') {
                    continue;
                }

                $info = self::derive($path);

                if ($info['controller'] !== null) {
                    $this->registerControllerRoutes($info, $existing);
                } else {
                    $this->registerViewRoute($path, $existing);
                }
            }
        });
    }

    private function registerControllerRoutes(array $info, array $existing): void
    {
        $controller = $info['controller'];
        $uri = trim($info['uri'], '/');
        $name = $info['route_name'];
        $param = $info['param'] ?? 'id';

        if ($uri === '') {
            return;
        }

        if (! in_array($uri, $existing, true)) {
            Route::get('/'.$uri, [$controller, 'index'])->middleware('web', 'auth')->name($name.'.index');
        }

        $maps = [
            'create' => ['get', '/'.$uri.'/create', $name.'.create'],
            'store' => ['post', '/'.$uri, $name.'.store'],
            'edit' => ['get', '/'.$uri.'/{'.$param.'}/edit', $name.'.edit'],
            'update' => ['put', '/'.$uri.'/{'.$param.'}', $name.'.update'],
            'destroy' => ['delete', '/'.$uri.'/{'.$param.'}', $name.'.destroy'],
        ];

        foreach ($maps as $method => [$verb, $routeUri, $routeName]) {
            if (method_exists($controller, $method) && ! in_array(trim($routeUri, '/'), $existing, true)) {
                Route::$verb($routeUri, [$controller, $method])->middleware('web', 'auth')->name($routeName);
            }
        }
    }

    private function registerViewRoute(string $path, array $existing): void
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        $uri = (string) end($segments);

        if (in_array($uri, $existing, true) || ! $this->viewExistsFor($path)) {
            return;
        }

        Route::get('/'.$uri, SubMenuViewController::class)
            ->middleware('web', 'auth')
            ->name('modul_view.'.implode('.', $segments));
    }

    private function viewExistsFor(string $path): bool
    {
        return view()->exists('moduls.'.str_replace('/', '.', trim($path, '/')));
    }

    private function subMenus(): array
    {
        return DB::table('sub_menu')
            ->where('file_sub_menu', '!=', '#')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->get(['sub_menu_id', 'file_sub_menu'])
            ->all();
    }

    public static function flushPathCache(): void
    {
        Cache::forget(self::PATH_CACHE_KEY);
        Cache::forget(self::LEGACY_PATH_CACHE_KEY);
        Cache::forget('sub_menu_view_by_uri');
    }
}
