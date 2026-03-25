<?php

namespace App\Services\Automation;

use App\Models\Opportunity;
use Illuminate\Support\Carbon;

class SlaFollowUpService
{
    public function run(int $slaHours = 72): int
    {
        $now = Carbon::now();
        $cutoff = $now->copy()->subHours($slaHours);
        $created = 0;

        $opportunities = Opportunity::query()
            ->with('contact')
            ->whereDoesntHave('deal')
            ->whereHas('contact.contactInteractions', fn ($query) => $query
                ->where('happened_at', '<=', $cutoff))
            ->whereDoesntHave('contact.contactInteractions', fn ($query) => $query
                ->where('happened_at', '>', $cutoff))
            ->orderByDesc('id')
            ->get();

        foreach ($opportunities as $opportunity) {
            $hasOpenSlaTask = $opportunity->tasks()
                ->where('task_type', 'sla_follow_up')
                ->whereNull('completed_at')
                ->exists();

            if ($hasOpenSlaTask) {
                continue;
            }

            $opportunity->tasks()->create([
                'assigned_user_id' => $opportunity->owner_user_id,
                'title' => 'SLA takip gorevi',
                'priority' => 'high',
                'task_type' => 'sla_follow_up',
                'due_at' => $now->copy()->addHours(4),
            ]);

            $created++;
        }

        return $created;
    }
}
