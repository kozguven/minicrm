<?php

namespace App\Services\Reports;

use App\Models\Opportunity;
use App\Models\OpportunityStage;
use Illuminate\Support\Collection;

class PipelineMetricsService
{
    /**
     * @return array{stages: Collection<int, OpportunityStage>}
     */
    public function pipeline(): array
    {
        $stages = OpportunityStage::query()
            ->withCount('opportunities')
            ->withSum('opportunities as opportunities_value_sum', 'value')
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return [
            'stages' => $stages,
        ];
    }

    /**
     * @return array{commit_forecast: float, best_case_forecast: float, open_opportunities: int}
     */
    public function forecast(): array
    {
        $openOpportunities = Opportunity::query()
            ->whereDoesntHave('deal')
            ->get();

        $commitForecast = (float) $openOpportunities
            ->where('health_status', 'commit')
            ->sum(fn (Opportunity $opportunity): float => (float) ($opportunity->value ?? 0));

        $bestCaseForecast = (float) $openOpportunities
            ->sum(function (Opportunity $opportunity): float {
                $value = (float) ($opportunity->value ?? 0);
                $probability = max(0, min(100, (int) ($opportunity->probability ?? 0)));

                return $value * ($probability / 100);
            });

        return [
            'commit_forecast' => $commitForecast,
            'best_case_forecast' => $bestCaseForecast,
            'open_opportunities' => $openOpportunities->count(),
        ];
    }
}
