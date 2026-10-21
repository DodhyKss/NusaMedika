<?php

namespace App\View\Components;

use App\Models\Bagian;
use Illuminate\View\Component;

class SelectPoliklinik extends Component
{
    public $poliklinik;

    public $selected;

    public $name;

    public $id;

    public $label;

    public $placeholder;

    public $required;

    public function __construct(
        $selected = '',
        $name = 'poliklinik',
        $id = 'poliklinik',
        $label = 'Pilih Poliklinik',
        $placeholder = 'Semua Poliklinik',
        $required = false
    ) {
        $this->selected = $selected;
        $this->name = $name;
        $this->id = $id;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->required = $required;

        $this->poliklinik = Bagian::orderBy('nama_bagian')
            ->where('referensi_bagian_id', env('REF_BAGIAN_RAJAL'))
            ->where(function ($q) {
                $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
            })
            ->get();
    }

    public function render()
    {
        return view('components.select_poliklinik');
    }
}
