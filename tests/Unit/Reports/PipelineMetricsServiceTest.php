<?php

namespace Tests\Unit\Reports;

use App\Models\Opportunity;
use App\Services\Reports\PipelineMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_best_case_forecast_uses_value_times_probability_rule(): void
    {
        Opportunity::factory()->create([
            'value' => 10000,
            'probability' => 50,
            'health_status' => 'watch',
        ]);

        Opportunity::factory()->create([
            'value' => 8000,
            'probability' => 25,
            'health_status' => 'commit',
        ]);

        $forecast = app(PipelineMetricsService::class)->forecast();

        $this->assertSame(8000.0, $forecast['commit_forecast']);
        $this->assertSame(7000.0, $forecast['best_case_forecast']);
    }
}
