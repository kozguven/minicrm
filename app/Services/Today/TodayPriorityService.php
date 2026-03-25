<?php

namespace App\Services\Today;

use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\CrmTask;
use App\Models\Opportunity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TodayPriorityService
{
    /**
     * @return list<array{type: string, title: string, empty_message: string, priority: int, items: Collection<int, mixed>}>
     */
    public function build(): array
    {
        $sections = [
            [
                'type' => 'critical_follow_up',
                'title' => 'Kritik Takipler',
                'empty_message' => 'Kritik takip bulunmuyor.',
                'priority' => 1,
                'items' => collect(),
            ],
            [
                'type' => 'overdue_next_step',
                'title' => 'Geciken Next-step',
                'empty_message' => 'Geciken next-step yok.',
                'priority' => 2,
                'items' => collect(),
            ],
            [
                'type' => 'sla_violation',
                'title' => 'SLA Ihlalleri',
                'empty_message' => 'SLA ihlali yok.',
                'priority' => 3,
                'items' => collect(),
            ],
            [
                'type' => 'call',
                'title' => 'Aranacak Kişiler',
                'empty_message' => 'Bugün için aranacak kişi yok.',
                'priority' => 4,
                'items' => collect(),
            ],
            [
                'type' => 'critical_opportunity',
                'title' => 'Kritik Fırsatlar',
                'empty_message' => 'Bugün için kritik fırsat yok.',
                'priority' => 5,
                'items' => collect(),
            ],
            [
                'type' => 'overdue_task',
                'title' => 'Geciken Görevler',
                'empty_message' => 'Geciken görev yok.',
                'priority' => 6,
                'items' => collect(),
            ],
            [
                'type' => 'due_follow_up',
                'title' => 'Takip Edilecek Görüşmeler',
                'empty_message' => 'Takip bekleyen görüşme yok.',
                'priority' => 7,
                'items' => collect(),
            ],
        ];

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $sections[0]['items'] = ContactInteraction::query()
            ->with(['contact.company', 'user'])
            ->whereNotNull('follow_up_due_at')
            ->whereNull('follow_up_completed_at')
            ->where('follow_up_due_at', '<=', $now)
            ->whereHas('contact', function ($query): void {
                $query
                    ->where('priority', 'high')
                    ->orWhereHas('opportunities', fn ($opportunityQuery) => $opportunityQuery
                        ->whereDoesntHave('deal')
                        ->where('health_status', 'risk'));
            })
            ->orderBy('follow_up_due_at')
            ->orderByDesc('happened_at')
            ->get();

        $sections[1]['items'] = Opportunity::query()
            ->with(['contact.company', 'opportunityStage'])
            ->whereDoesntHave('deal')
            ->whereNotNull('next_step_due_at')
            ->where('next_step_due_at', '<', $now)
            ->orderBy('next_step_due_at')
            ->orderByDesc('value')
            ->get();

        $sections[2]['items'] = CrmTask::query()
            ->with(['opportunity.contact.company'])
            ->where('task_type', 'sla_follow_up')
            ->whereNull('completed_at')
            ->orderBy('due_at')
            ->orderByDesc('id')
            ->get();

        $sections[3]['items'] = Contact::query()
            ->with([
                'company',
                'opportunities' => fn ($query) => $query
                    ->whereDate('expected_close_date', $today)
                    ->whereDoesntHave('deal')
                    ->orderBy('expected_close_date')
                    ->orderBy('title'),
            ])
            ->whereNotNull('phone')
            ->whereHas('opportunities', fn ($query) => $query
                ->whereDate('expected_close_date', $today)
                ->whereDoesntHave('deal'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $sections[4]['items'] = Opportunity::query()
            ->with(['contact.company', 'opportunityStage'])
            ->whereDate('expected_close_date', '<', $today)
            ->whereDoesntHave('deal')
            ->orderBy('expected_close_date')
            ->orderByDesc('value')
            ->orderBy('title')
            ->get();

        $sections[5]['items'] = CrmTask::query()
            ->with(['opportunity.contact.company'])
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', $now)
            ->orderBy('due_at')
            ->orderBy('title')
            ->get();

        $sections[6]['items'] = ContactInteraction::query()
            ->with(['contact.company', 'user'])
            ->whereNotNull('follow_up_due_at')
            ->whereNull('follow_up_completed_at')
            ->where('follow_up_due_at', '<=', $now)
            ->orderBy('follow_up_due_at')
            ->orderByDesc('happened_at')
            ->get();

        usort($sections, fn (array $left, array $right) => $left['priority'] <=> $right['priority']);

        return $sections;
    }
}
