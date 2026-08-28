<?php

namespace App\Http\Controllers\SubMenu;

use App\Http\Controllers\Controller;

class SubMenuViewController extends Controller
{
    public function __invoke()
    {
        $path = $this->sanitizePath(ltrim(request()->path(), '/'));

        if ($path === null || ! view()->exists('moduls.'.$path)) {
            abort(404);
        }

        return view('moduls.'.$path);
    }

    private function sanitizePath(string $path)
    {
        $path = trim($path, '/');

        if ($path === '' || ! preg_match('#^[a-zA-Z0-9_\-]+(?:/[a-zA-Z0-9_\-]+)*$#', $path)) {
            return null;
        }

        return str_replace('/', '.', $path);
    }
}
