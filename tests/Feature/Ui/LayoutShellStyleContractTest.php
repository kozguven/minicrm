<?php

namespace Tests\Feature\Ui;

use Tests\TestCase;

class LayoutShellStyleContractTest extends TestCase
{
    public function test_panels_are_centered_when_using_compact_max_widths(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.panel {', $css);
        $this->assertStringContainsString('margin-inline: auto;', $css);
    }
}
