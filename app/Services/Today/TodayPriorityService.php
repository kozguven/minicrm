<?php

namespace App\Services\Today;

use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\Permissions\PermissionResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TodayPriorityService
{
    public function __construct(
        private readonly PermissionResolver $permissionResolver,
    ) {
    }

    /**
     * @return list<array{type: string, title: string, empty_message: string, priority: int, items: Collection<int, mixed>}>
     */
    public function buildFor(User $user): array
    {
        $sections = [
            [
                'type' => 'call',
                'title' => 'Aranacak Kisiler',
                'empty_message' => 'Bugun icin aranacak kisi yok.',
                'priority' => 1,
                'items' => collect(),
            ],
            [
                'type' => 'critical_opportunity',
                'title' => 'Kritik Firsatlar',
                'empty_message' => 'Bugun icin kritik firsat yok.',
                'priority' => 2,
                'items' => collect(),
            ],
            [
                'type' => 'overdue_task',
                'title' => 'Geciken Gorevler',
                'empty_message' => 'Geciken gorev yok.',
                'priority' => 3,
                'items' => collect(),
            ],
        ];

        if (! $this->permissionResolver->can($user, 'companies.view')) {
            return $sections;
        }

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

        usort($sections, fn (array $left, array $right) => $left['priority'] <=> $right['priority']);

        return $sections;
    }
}
