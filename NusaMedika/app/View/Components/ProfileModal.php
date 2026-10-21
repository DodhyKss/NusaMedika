<?php

namespace App\View\Components;

use App\Models\Bagian;
use App\Models\Jabatan;
use App\Models\Profesi;
use App\Models\StatusKepegawaian;
use Illuminate\View\Component;

class ProfileModal extends Component
{
    /** User yang sedang login. */
    public $user;

    /** Record pegawai milik user (null bila akun tidak terhubung ke pegawai). */
    public $pegawai;

    /** [label kategori => Collection<Bagian>] untuk dropdown bagian. */
    public $bagianGroups;

    public $profesi;

    public $jabatan;

    public $statusKepegawaian;

    /**
     * Label kategori bagian, mengikuti referensi_bagian_id.
     * Hanya 1-3 yang punya konstanta env; sisanya dipakai Inventory/Farmasi/Penunjang.
     */
    public const KATEGORI_BAGIAN = [
        1 => 'Poli / Rawat Jalan',
        2 => 'Ruang Perawatan',
        3 => 'Gawat Darurat',
        4 => 'Gudang',
        5 => 'Depo',
        6 => 'Penunjang Medis',
    ];

    public function __construct()
    {
        $this->user = auth()->user();
        $this->pegawai = $this->user?->pegawai;

        $bagian = Bagian::aktif()->orderBy('nama_bagian')->get();
        $this->bagianGroups = collect(self::KATEGORI_BAGIAN)
            ->map(fn ($label, $refId) => [
                'label' => $label,
                'items' => $bagian->where('referensi_bagian_id', $refId)->values(),
            ])
            ->filter(fn ($group) => $group['items']->isNotEmpty())
            ->values();

        $this->profesi = Profesi::orderBy('nama_profesi')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->get();

        $this->jabatan = Jabatan::orderBy('nama_jabatan')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->get();

        $this->statusKepegawaian = StatusKepegawaian::orderBy('nama_status_kepegawaian')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->get();
    }

    public function render()
    {
        return view('components.profile_modal');
    }
}
