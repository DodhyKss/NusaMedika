<?php

namespace Tests\Feature;

use App\Helpers\SelectOption;
use Tests\TestCase;

class SelectOptionRenderTest extends TestCase
{
    public function test_render_menghasilkan_option_aktif(): void
    {
        $html = SelectOption::render('jenis_kelamin', 'L');

        $this->assertStringContainsString('value="L"', $html);
        $this->assertStringContainsString('Laki-laki', $html);
        $this->assertStringContainsString('selected', $html);
        $this->assertStringContainsString('value="P"', $html);
    }

    public function test_render_dengan_placeholder(): void
    {
        $html = SelectOption::render('jenis_kelamin', null, '-- Pilih --');

        $this->assertStringContainsString('value=""', $html);
        $this->assertStringContainsString('-- Pilih --', $html);
        $this->assertStringNotContainsString('selected', $html);
    }

    public function test_render_kunci_tidak_dikenal_kembali_polos(): void
    {
        $this->assertSame('', SelectOption::render('tidak_ada'));
    }
}
