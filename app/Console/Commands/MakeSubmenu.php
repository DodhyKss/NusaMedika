<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeSubmenu extends Command
{
    protected $signature = 'make:submenu
                            {path : Path submenu (contoh: Administrator/ManajemenMaster/Barang atau Registrasi/Pasien/DaftarBarang)}
                            {--force : Lewati konfirmasi dan langsung buat file}';

    protected $description = 'Membuat template controller & blade view untuk sub_menu baru secara otomatis';

    public function handle(): int
    {
        $path = $this->argument('path');

        $segments = array_filter(explode('/', trim($path, '/')));

        if (count($segments) < 2) {
            $this->error('Path minimal 2 segmen. Contoh: Administrator/Barang atau Registrasi/Pasien/DaftarBarang');

            return self::FAILURE;
        }

        $leaf = end($segments);
        $basename = $this->toSnake($leaf);
        $model = $leaf;
        $rawPath = implode('/', $segments);
        $controllerDir = base_path('app/Http/Controllers/'.$rawPath);
        $viewDir = base_path('resources/views/moduls/'.$rawPath);
        $prefix = $segments[0] === 'Administrator' ? 'admin.' : '';
        $routePrefix = $prefix.$basename;

        $this->line('');
        $this->line('  <info>Path:</info>      '.$path);
        $this->line('  <info>Model:</info>     '.$model);
        $this->line('  <info>Controller:</info>'.$controllerDir.'/'.$leaf.'Controller.php');
        $this->line('  <info>View:</info>      '.$viewDir.'/'.$basename.'.blade.php');
        $this->line('  <info>Route:</info>     '.$routePrefix.'.*');
        $this->line('');

        if (! $this->option('force') && ! $this->confirm('Buat file-file di atas?')) {
            $this->info('Dibatalkan.');

            return self::SUCCESS;
        }

        if (! is_dir($controllerDir)) {
            mkdir($controllerDir, 0755, true);
        }
        if (! is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }

        $this->generateController($controllerDir, $leaf, $model, $rawPath, $routePrefix, $basename);
        $this->generateIndexView($viewDir, $basename, $model);
        $this->generateCreateView($viewDir, $basename, $model);
        $this->generateEditView($viewDir, $basename, $model);

        $this->info('Berhasil dibuat:');
        $this->line('  <comment>Controller:</comment> app/Http/Controllers/'.$rawPath.'/'.$leaf.'Controller.php');
        $this->line('  <comment>View index:</comment>   resources/views/moduls/'.$rawPath.'/'.$basename.'.blade.php');
        $this->line('  <comment>View create:</comment>  resources/views/moduls/'.$rawPath.'/'.$basename.'_create.blade.php');
        $this->line('  <comment>View edit:</comment>    resources/views/moduls/'.$rawPath.'/'.$basename.'_edit.blade.php');
        $this->line('');
        $this->info('Route otomatis: '.$routePrefix.'.index, '.$routePrefix.'.create, '.$routePrefix.'.store, '.$routePrefix.'.edit, '.$routePrefix.'.update, '.$routePrefix.'.destroy');
        $this->line('');
        $this->info('Langkah berikutnya:');
        $this->line('  1. Buat/tambah kolom di tabel & model <info>App\Models\\'.$model.'</info>');
        $this->line('  2. Tambahkan validation rules di controller');
        $this->line('  3. Isi field form di blade create & edit');
        $this->line('  4. Tambahkan sub_menu di tabel <info>sub_menu</info> dengan <info>file_sub_menu = \''.$rawPath.'/'.$basename.'\'</info>');
        $this->line('  5. Jalankan <info>php artisan route:clear</info> lalu <info>php artisan cache:clear</info>');

        return self::SUCCESS;
    }

    private function generateController(string $dir, string $leaf, string $model, string $rawPath, string $routePrefix, string $basename): void
    {
        $namespace = 'App\\Http\\Controllers\\'.str_replace('/', '\\', $rawPath);
        $viewDots = 'moduls.'.str_replace('/', '.', $rawPath);

        $content = <<<'PHP'
<?php

namespace {NAMESPACE};

use App\Http\Controllers\Controller;
use App\Models\{MODEL};
use Illuminate\Http\Request;

class {LEAF}Controller extends Controller
{
    public function index()
    {
        // Logika untuk menampilkan daftar (INDEX) di sini.
        // return view('{VIEW_PATH}.{BASENAME}', compact('...'));
    }

    public function create()
    {
        // Logika untuk menampilkan form tambah (CREATE) di sini.
        // return view('{VIEW_PATH}.{BASENAME}_create');
    }

    public function store(Request $request)
    {
        // Logika untuk menyimpan data baru di sini.
    }

    public function edit($id)
    {
        // Logika untuk menampilkan form ubah (EDIT) di sini.
        // return view('{VIEW_PATH}.{BASENAME}_edit', compact('...'));
    }

    public function update(Request $request, $id)
    {
        // Logika untuk memperbarui data di sini.
    }

    public function destroy($id)
    {
        // Logika untuk menghapus data di sini.
    }
}
PHP;

        $replacements = [
            '{NAMESPACE}' => $namespace,
            '{MODEL}' => $model,
            '{LEAF}' => $leaf,
            '{VIEW_PATH}' => $viewDots,
            '{BASENAME}' => $basename,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $file = $dir.'/'.$leaf.'Controller.php';
        file_put_contents($file, $content);
    }

    private function generateIndexView(string $dir, string $basename, string $model): void
    {
        $title = $model.' - INDEX';

        $content = <<<'BLADE'
@extends('layouts.app')

@section('content')
<x-page-header title="[[TITLE]]" subtitle="Halaman index [[MODEL_LOWER]]." />
@endsection
BLADE;

        $replacements = [
            '[[TITLE]]' => $title,
            '[[MODEL_LOWER]]' => lcfirst($model),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        file_put_contents($dir.'/'.$basename.'.blade.php', $content);
    }

    private function generateCreateView(string $dir, string $basename, string $model): void
    {
        $title = $model.' - CREATE';

        $content = <<<'BLADE'
@extends('layouts.app')

@section('content')
<x-page-header title="[[TITLE]]" subtitle="Halaman create [[MODEL_LOWER]]." />
@endsection
BLADE;

        $replacements = [
            '[[TITLE]]' => $title,
            '[[MODEL_LOWER]]' => lcfirst($model),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        file_put_contents($dir.'/'.$basename.'_create.blade.php', $content);
    }

    private function generateEditView(string $dir, string $basename, string $model): void
    {
        $title = $model.' - EDIT';

        $content = <<<'BLADE'
@extends('layouts.app')

@section('content')
<x-page-header title="[[TITLE]]" subtitle="Halaman edit [[MODEL_LOWER]]." />
@endsection
BLADE;

        $replacements = [
            '[[TITLE]]' => $title,
            '[[MODEL_LOWER]]' => lcfirst($model),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        file_put_contents($dir.'/'.$basename.'_edit.blade.php', $content);
    }

    private function toSnake(string $value): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }
}
