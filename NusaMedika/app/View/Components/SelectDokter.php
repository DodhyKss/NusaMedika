<?php

namespace App\View\Components;

use App\Models\Pegawai;
use Illuminate\View\Component;

class SelectDokter extends Component
{
    public $dokters;

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
        $label = 'Dokter',
        $placeholder = '-- Pilih Dokter --',
        $required = false
    ) {
        $this->selected = $selected;
        $this->name = $name;
        $this->id = $id;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->required = $required;

        $this->dokters = Pegawai::where('profesi_id', 1)
            ->where(function ($q) {
                $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
            })
            ->orderBy('nama_pegawai')
            ->get();
    }

    public function render()
    {
        return view('components.select_dokter');
    }
}
