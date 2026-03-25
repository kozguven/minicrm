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
                'type' => 'call',
                'title' => 'Aranacak Kişiler',
                'empty_message' => 'Bugün için aranacak kişi yok.',
                'priority' => 1,
                'items' => collect(),
            ],
            [
                'type' => 'critical_opportunity',
                'title' => 'Kritik Fırsatlar',
                'empty_message' => 'Bugün için kritik fırsat yok.',
                'priority' => 2,
                'items' => collect(),
            ],
            [
                'type' => 'overdue_task',
                'title' => 'Geciken Görevler',
                'empty_message' => 'Geciken görev yok.',
                'priority' => 3,
                'items' => collect(),
            ],
            [
                'type' => 'due_follow_up',
                'title' => 'Takip Edilecek Görüşmeler',
                'empty_message' => 'Takip bekleyen görüşme yok.',
                'priority' => 4,
                'items' => collect(),
            ],
        ];

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $sections[0]['items'] = Contact::query()
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

        $sections[1]['items'] = Opportunity::query()
            ->with(['contact.company', 'opportunityStage'])
            ->whereDate('expected_close_date', '<', $today)
            ->whereDoesntHave('deal')
            ->orderBy('expected_close_date')
            ->orderByDesc('value')
            ->orderBy('title')
            ->get();

        $sections[2]['items'] = CrmTask::query()
            ->with(['opportunity.contact.company'])
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', $now)
            ->orderBy('due_at')
            ->orderBy('title')
            ->get();

        $sections[3]['items'] = ContactInteraction::query()
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
