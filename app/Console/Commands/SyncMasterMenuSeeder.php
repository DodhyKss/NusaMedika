<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncMasterMenuSeeder extends Command
{
    protected $signature = 'seeder:sync-master-menu';

    protected $description = 'Menulis ulang ModulMenuSubMenuSeeder.php dari data modul/menu/sub_menu di database (record aktif saja)';

    public function handle(): int
    {
        $aktif = function ($query) {
            return $query->where(function ($q) {
                $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
            });
        };

        $moduls = $aktif(DB::table('modul'))
            ->orderBy('urutan_modul')
            ->orderBy('modul_id')
            ->get(['modul_id', 'nama_modul', 'icon_modul', 'urutan_modul']);

        $menus = $aktif(DB::table('menu'))
            ->orderBy('modul_id')
            ->orderBy('urutan_menu')
            ->orderBy('menu_id')
            ->get(['menu_id', 'modul_id', 'nama_menu', 'urutan_menu']);

        $subMenus = $aktif(DB::table('sub_menu'))
            ->orderBy('menu_id')
            ->orderBy('urutan_sub_menu')
            ->orderBy('sub_menu_id')
            ->get(['sub_menu_id', 'menu_id', 'nama_sub_menu', 'file_sub_menu', 'urutan_sub_menu']);

        $modulNames = $moduls->pluck('nama_modul', 'modul_id');
        $menuNames = $menus->pluck('nama_menu', 'menu_id');
        $menuModul = $menus->pluck('modul_id', 'menu_id');

        $modulLines = $moduls->map(fn ($m) => sprintf(
            "            ['modul_id' => %s, 'nama_modul' => %s, 'icon_modul' => %s, 'urutan_modul' => %s],",
            $this->lit($m->modul_id),
            $this->lit($m->nama_modul),
            $this->lit($m->icon_modul),
            $this->lit($m->urutan_modul)
        ))->implode(PHP_EOL).PHP_EOL;

        $menuLines = '';
        $currentModul = null;
        foreach ($menus as $menu) {
            if ($menu->modul_id !== $currentModul) {
                $currentModul = $menu->modul_id;
                $menuLines .= sprintf(
                    "            // Modul %s (%s)\n",
                    $modulNames[$menu->modul_id] ?? 'Tanpa Modul',
                    $this->lit($menu->modul_id)
                );
            }
            $menuLines .= sprintf(
                "            ['menu_id' => %s, 'modul_id' => %s, 'nama_menu' => %s, 'urutan_menu' => %s],\n",
                $this->lit($menu->menu_id),
                $this->lit($menu->modul_id),
                $this->lit($menu->nama_menu),
                $this->lit($menu->urutan_menu)
            );
        }

        $subMenuLines = '';
        $currentMenu = null;
        foreach ($subMenus as $subMenu) {
            if ($subMenu->menu_id !== $currentMenu) {
                $currentMenu = $subMenu->menu_id;
                $modulId = $menuModul[$subMenu->menu_id] ?? null;
                $subMenuLines .= sprintf(
                    "            // Menu %s (%s) - %s\n",
                    $menuNames[$subMenu->menu_id] ?? 'Tanpa Menu',
                    $this->lit($subMenu->menu_id),
                    $modulId !== null ? ($modulNames[$modulId] ?? 'Tanpa Modul') : 'Tanpa Modul'
                );
            }
            $subMenuLines .= sprintf(
                "            ['sub_menu_id' => %s, 'menu_id' => %s, 'nama_sub_menu' => %s, 'file_sub_menu' => %s, 'urutan_sub_menu' => %s],\n",
                $this->lit($subMenu->sub_menu_id),
                $this->lit($subMenu->menu_id),
                $this->lit($subMenu->nama_sub_menu),
                $this->lit($subMenu->file_sub_menu),
                $this->lit($subMenu->urutan_sub_menu)
            );
        }

        $content = <<<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulMenuSubMenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ======== Seeder Module =========
        $moduls = [
__MODULS__        ];

        foreach ($moduls as $modul) {
            DB::table('modul')->updateOrInsert(
                ['modul_id' => $modul['modul_id']],
                array_merge($modul, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        // ========= Seeder Menu ==========
        $menus = [
__MENUS__        ];

        foreach ($menus as $menu) {
            DB::table('menu')->updateOrInsert(
                ['menu_id' => $menu['menu_id']],
                array_merge($menu, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        // ========= Seeder Sub Menu ==========
        // file_sub_menu = path view lengkap BESERTA nama file blade (tanpa .blade.php).
        // Segmen terakhir (basename) = URI & nama route; folder = namespace controller & path view.
        $subMenus = [
__SUBMENUS__        ];

        foreach ($subMenus as $subMenu) {
            DB::table('sub_menu')->updateOrInsert(
                ['sub_menu_id' => $subMenu['sub_menu_id']],
                array_merge($subMenu, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}

PHP;

        $content = str_replace('__MODULS__', $modulLines, $content);
        $content = str_replace('__MENUS__', $menuLines === '' ? '' : rtrim($menuLines, PHP_EOL).PHP_EOL, $content);
        $content = str_replace('__SUBMENUS__', $subMenuLines === '' ? '' : rtrim($subMenuLines, PHP_EOL).PHP_EOL, $content);

        file_put_contents(database_path('seeders/ModulMenuSubMenuSeeder.php'), $content);

        $this->info(sprintf(
            'ModulMenuSubMenuSeeder berhasil diperbarui: %d modul, %d menu, %d sub_menu.',
            $moduls->count(),
            $menus->count(),
            $subMenus->count()
        ));

        return self::SUCCESS;
    }

    private function lit($value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_null($value)) {
            return 'null';
        }

        return "'".str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $value)."'";
    }
}
