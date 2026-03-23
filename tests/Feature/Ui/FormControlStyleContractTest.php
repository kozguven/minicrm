<?php

namespace Tests\Feature\Ui;

use Tests\TestCase;

class FormControlStyleContractTest extends TestCase
{
    public function test_select_controls_define_normalization_rules_for_consistent_rendering(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.select {', $css);
        $this->assertStringContainsString('appearance: none;', $css);
        $this->assertStringContainsString('background-image:', $css);
        $this->assertStringContainsString('padding-right:', $css);
    }
}
