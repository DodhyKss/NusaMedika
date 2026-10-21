<?php

namespace App\Console\Commands\Make;

use Illuminate\Foundation\Console\ModelMakeCommand as BaseModelMakeCommand;

class ModelMakeCommand extends BaseModelMakeCommand
{
    /**
     * Nama tabel dijamin singular (tidak di-plural-kan) dan akronim tidak dipecah.
     *
     * Contoh: Barang -> barang, KategoriSPM -> kategori_spm,
     * DaftarGawatDarurat -> daftar_gawat_darurat.
     */
    protected function tableName(): string
    {
        $name = class_basename($this->argument('name'));

        $snake = preg_replace('/(.)([A-Z][a-z]+)/', '$1_$2', $name);
        $snake = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $snake);

        return strtolower($snake);
    }

    /**
     * Buat file migration memakai nama tabel tunggal, misal create_barang_table.
     */
    protected function createMigration()
    {
        $table = $this->tableName();

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }

    /**
     * Sisipkan properti $table agar Eloquent memakai tabel yang sama
     * dengan migration (tanpa pluralisasi default).
     */
    protected function buildClass($name)
    {
        return str_replace('{{ table }}', $this->tableName(), parent::buildClass($name));
    }
}
