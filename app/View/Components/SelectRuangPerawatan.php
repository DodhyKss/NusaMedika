<?php

namespace App\View\Components;

use App\Models\Bagian;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectRuangPerawatan extends Component
{
    public $ruangan;
    public $selected;
    public $name;
    public $id;

    public function __construct($selected = '', $name = 'ruangan', $id = 'ruangan')
    {
        $this->selected = $selected;
        $this->name = $name;
        $this->id = $id;
        $this->ruangan = Bagian::orderBy('nama_bagian')->whereNull('status_batal')->where('referensi_bagian', env('REF_BAGIAN_RANAP'))->get(); 
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.select-ruang-perawatan');
    }
}
