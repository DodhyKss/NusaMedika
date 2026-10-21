<?php

namespace App\View\Components;

use App\Models\Pegawai;
use Illuminate\View\Component;

/**
 * Dropdown pegawai generik (bukan hanya dokter).
 *
 * Dipakai form SBAR untuk kolom "Penerima Informasi" — serah terima antar shift
 * juga sering antar perawat, bukan antar dokter saja. Filter profesi opsional
 * lewat prop `profesiId`.
 */
class SelectPegawai extends Component
{
    public $pegawais;

    public $selected;

    public $name;

    public $id;

    public $label;

    public $placeholder;

    public $required;

    public function __construct(
        $selected = '',
        $name = 'pegawai_id',
        $id = 'pegawai_id',
        $label = 'Pegawai',
        $placeholder = '-- Pilih Pegawai --',
        $required = false,
        $profesiId = null
    ) {
        $this->selected = $selected;
        $this->name = $name;
        $this->id = $id;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->required = $required;

        $query = Pegawai::query()
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', '!=', 1);
            });

        if ($profesiId) {
            $query->where('profesi_id', (int) $profesiId);
        }

        $this->pegawais = $query->orderBy('nama_pegawai')->get();
    }

    public function render()
    {
        return view('components.select_pegawai');
    }
}
