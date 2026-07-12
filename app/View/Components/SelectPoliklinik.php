<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Bagian;

class SelectPoliklinik extends Component
{
    public $poliklinik;
    public $selected;
    public $name;
    public $id;

    public function __construct(
        $selected = '',
        $name = 'poliklinik',
        $id = 'poliklinik'
    ) {
        $this->selected = $selected;
        $this->name = $name;
        $this->id = $id;

        $this->poliklinik = Bagian::orderBy('nama_bagian')->whereNull('status_batal')->where('referensi_bagian', env('REF_BAGIAN_RAJAL'))->get();
    }

    public function render()
    {
        return view('components.select_poliklinik');
    }
}