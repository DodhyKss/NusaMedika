<?php

namespace Tests\Unit;

use App\Helpers\SelectOption;
use PHPUnit\Framework\TestCase;

class SelectOptionTest extends TestCase
{
    public function test_all_memiliki_key_dropdown_utama(): void
    {
        $data = SelectOption::all();

        $this->assertArrayHasKey('jenis_kelamin', $data);
        $this->assertArrayHasKey('agama', $data);
        $this->assertArrayHasKey('golongan_darah', $data);
        $this->assertArrayHasKey('status_perkawinan', $data);
        $this->assertArrayHasKey('kebangsaan', $data);
        $this->assertArrayHasKey('kategori_icd', $data);
    }

    public function test_all_setiap_option_memiliki_value_dan_label(): void
    {
        foreach (SelectOption::all() as $key => $options) {
            foreach ($options as $option) {
                $this->assertArrayHasKey('value', $option, "[$key] option tanpa 'value'");
                $this->assertArrayHasKey('label', $option, "[$key] option tanpa 'label'");
                $this->assertNotEquals('', (string) $option['value'], "[$key] value kosong");
                $this->assertNotEquals('', (string) $option['label'], "[$key] label kosong");
            }
        }
    }

    public function test_get_mengembalikan_semua_jenis_kelamin(): void
    {
        $opsi = SelectOption::get('jenis_kelamin');

        $this->assertCount(2, $opsi);
        $this->assertSame('L', $opsi[0]['value']);
        $this->assertSame('Laki-laki', $opsi[0]['label']);
        $this->assertSame('P', $opsi[1]['value']);
        $this->assertSame('Perempuan', $opsi[1]['label']);
    }

    public function test_get_key_tidak_dikenal_mengembalikan_array_kosong(): void
    {
        $this->assertSame([], SelectOption::get('tidak_ada'));
        $this->assertSame([], SelectOption::get(''));
    }

    public function test_triase_igd_mempunyai_class_warna(): void
    {
        $opsi = SelectOption::get('triase_igd');

        $this->assertSame('text-red-600', $opsi[0]['class']);
        $this->assertSame('text-emerald-500', $opsi[2]['class']);
    }
}
