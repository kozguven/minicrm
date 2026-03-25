<?php

namespace Tests\Unit\Automation;

use App\Models\OpportunityStage;
use App\Services\Automation\StageTaskTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageTaskTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_stage_specific_template_for_teklif_stage(): void
    {
        $stage = OpportunityStage::factory()->create(['name' => 'Teklif']);

        $template = app(StageTaskTemplateService::class)->templateForStage($stage);

        $this->assertSame('Teklif asamasi teklif dosyasini guncelle', $template['title']);
        $this->assertSame('high', $template['priority']);
        $this->assertSame(6, $template['due_in_hours']);
    }

    public function test_returns_default_template_for_unknown_stage(): void
    {
        $stage = OpportunityStage::factory()->create(['name' => 'Kesif']);

        $template = app(StageTaskTemplateService::class)->templateForStage($stage);

        $this->assertSame('Kesif asamasi sonrasi takip gorevi', $template['title']);
        $this->assertSame('medium', $template['priority']);
        $this->assertSame(24, $template['due_in_hours']);
    }
}
