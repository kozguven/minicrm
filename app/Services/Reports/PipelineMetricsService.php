<?php

namespace App\Services\Reports;

use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\CrmTask;
use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\User;
use Illuminate\Support\Carbon;
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

    /**
     * @return array{
     *     total_leads: int,
     *     qualified_leads: int,
     *     won_deals: int,
     *     lead_to_qualified_rate: float,
     *     qualified_to_won_rate: float,
     *     lead_to_won_rate: float
     * }
     */
    public function funnel(): array
    {
        $totalLeads = Contact::query()->count();
        $qualifiedLeads = Contact::query()
            ->where('lead_status', 'qualified')
            ->count();
        $wonDeals = Deal::query()->count();

        return [
            'total_leads' => $totalLeads,
            'qualified_leads' => $qualifiedLeads,
            'won_deals' => $wonDeals,
            'lead_to_qualified_rate' => $this->percentage($qualifiedLeads, $totalLeads),
            'qualified_to_won_rate' => $this->percentage($wonDeals, $qualifiedLeads),
            'lead_to_won_rate' => $this->percentage($wonDeals, $totalLeads),
        ];
    }

    /**
     * @return array{
     *     average_close_days: float,
     *     bottleneck_stage: string,
     *     bottleneck_avg_days: float,
     *     stage_aging: Collection<int, array{stage: string, avg_days: float, open_count: int}>
     * }
     */
    public function salesCycle(): array
    {
        $closedDeals = Deal::query()
            ->with('opportunity')
            ->whereNotNull('closed_at')
            ->get();

        $averageCloseDays = round(
            (float) $closedDeals->map(function (Deal $deal): float {
                $opportunityCreatedAt = $deal->opportunity?->created_at;
                $closedAt = $deal->closed_at;

                if (! $opportunityCreatedAt instanceof Carbon || ! $closedAt instanceof Carbon) {
                    return 0.0;
                }

                return (float) $opportunityCreatedAt->diffInDays($closedAt);
            })->avg(),
            1,
        );

        $stageAging = OpportunityStage::query()
            ->with(['opportunities' => fn ($query) => $query
                ->whereDoesntHave('deal')
                ->select(['id', 'opportunity_stage_id', 'created_at'])])
            ->orderBy('position')
            ->orderBy('name')
            ->get()
            ->map(function (OpportunityStage $stage): array {
                $ages = $stage->opportunities
                    ->map(function (Opportunity $opportunity): float {
                        if (! $opportunity->created_at instanceof Carbon) {
                            return 0.0;
                        }

                        return (float) $opportunity->created_at->diffInDays(now());
                    });

                return [
                    'stage' => $stage->name,
                    'avg_days' => round((float) $ages->avg(), 1),
                    'open_count' => $stage->opportunities->count(),
                ];
            });

        $bottleneck = $stageAging
            ->sortByDesc('avg_days')
            ->first();

        return [
            'average_close_days' => $averageCloseDays,
            'bottleneck_stage' => (string) ($bottleneck['stage'] ?? 'Veri yok'),
            'bottleneck_avg_days' => (float) ($bottleneck['avg_days'] ?? 0.0),
            'stage_aging' => $stageAging,
        ];
    }

    /**
     * @return array{users: Collection<int, array{
     *     name: string,
     *     open_tasks: int,
     *     overdue_rate: float,
     *     follow_up_completion_rate: float
     * }>}
     */
    public function performance(): array
    {
        $users = User::query()
            ->orderBy('name')
            ->get()
            ->map(function (User $user): array {
                $openTasks = CrmTask::query()
                    ->where('assigned_user_id', $user->id)
                    ->whereNull('completed_at')
                    ->count();

                $overdueTasks = CrmTask::query()
                    ->where('assigned_user_id', $user->id)
                    ->whereNull('completed_at')
                    ->whereNotNull('due_at')
                    ->where('due_at', '<', now())
                    ->count();

                $followUps = ContactInteraction::query()
                    ->where('user_id', $user->id)
                    ->whereNotNull('follow_up_due_at')
                    ->count();

                $completedFollowUps = ContactInteraction::query()
                    ->where('user_id', $user->id)
                    ->whereNotNull('follow_up_due_at')
                    ->whereNotNull('follow_up_completed_at')
                    ->count();

                return [
                    'name' => $user->name,
                    'open_tasks' => $openTasks,
                    'overdue_rate' => $this->percentage($overdueTasks, $openTasks),
                    'follow_up_completion_rate' => $this->percentage($completedFollowUps, $followUps),
                ];
            })
            ->filter(fn (array $item): bool => $item['open_tasks'] > 0 || $item['follow_up_completion_rate'] > 0)
            ->values();

        return [
            'users' => $users,
        ];
    }

    /**
     * @return array{
     *     missing_email: int,
     *     missing_phone: int,
     *     next_step_missing: int,
     *     unassigned_contacts: int,
     *     unassigned_opportunities: int,
     *     unassigned_tasks: int,
     *     unassigned_total: int
     * }
     */
    public function dataQuality(): array
    {
        $missingEmail = Contact::query()
            ->where(function ($query): void {
                $query->whereNull('email')->orWhere('email', '');
            })
            ->count();

        $missingPhone = Contact::query()
            ->where(function ($query): void {
                $query->whereNull('phone')->orWhere('phone', '');
            })
            ->count();

        $nextStepMissing = Opportunity::query()
            ->whereDoesntHave('deal')
            ->where(function ($query): void {
                $query->whereNull('next_step')->orWhere('next_step', '');
            })
            ->count();

        $unassignedContacts = Contact::query()->whereNull('owner_user_id')->count();
        $unassignedOpportunities = Opportunity::query()->whereNull('owner_user_id')->count();
        $unassignedTasks = CrmTask::query()->whereNull('assigned_user_id')->count();

        return [
            'missing_email' => $missingEmail,
            'missing_phone' => $missingPhone,
            'next_step_missing' => $nextStepMissing,
            'unassigned_contacts' => $unassignedContacts,
            'unassigned_opportunities' => $unassignedOpportunities,
            'unassigned_tasks' => $unassignedTasks,
            'unassigned_total' => $unassignedContacts + $unassignedOpportunities + $unassignedTasks,
        ];
    }

    private function percentage(int $value, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(($value / $total) * 100, 2);
    }
}
