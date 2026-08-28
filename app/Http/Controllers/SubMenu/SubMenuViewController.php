<?php

namespace App\Http\Controllers\SubMenu;

use App\Http\Controllers\Controller;
use App\Providers\SubMenuRouteServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SubMenuViewController extends Controller
{
    public function __invoke()
    {
        $currentUri = trim(request()->path(), '/');

        if ($currentUri === '') {
            abort(404);
        }

        $path = Cache::remember('sub_menu_view_by_uri', now()->addHours(24), function () {
            $map = [];

            foreach ($this->activeSubMenus() as $subMenu) {
                $fileSubMenu = (string) $subMenu->file_sub_menu;

                if ($fileSubMenu === '#' || trim($fileSubMenu, '/') === '') {
                    continue;
                }

                $info = SubMenuRouteServiceProvider::derive($fileSubMenu);

                if ($info['controller'] === null) {
                    $map[$info['uri']] = $fileSubMenu;
                }
            }

            return $map;
        });

        $fileSubMenu = $path[$currentUri] ?? $this->legacyPath($currentUri);

        if ($fileSubMenu === null) {
            abort(404);
        }

        $view = str_replace('/', '.', trim($fileSubMenu, '/'));

        if (view()->exists('moduls.'.$view.'.index')) {
            return view('moduls.'.$view.'.index');
        }

        if (view()->exists('moduls.'.$view)) {
            return view('moduls.'.$view);
        }

        if (view()->exists('moduls.'.$view.'.form')) {
            return view('moduls.'.$view.'.form');
        }

        abort(404);
    }

    private function legacyPath(string $uri): ?string
    {
        if (! preg_match('#^[a-zA-Z0-9_\-]+(?:/[a-zA-Z0-9_\-]+)*$#', $uri)) {
            return null;
        }

        return view()->exists('moduls.'.str_replace('/', '.', $uri)) ? $uri : null;
    }

    private function activeSubMenus(): array
    {
        return DB::table('sub_menu')
            ->where('file_sub_menu', '!=', '#')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->get(['sub_menu_id', 'file_sub_menu']);
    }
}
