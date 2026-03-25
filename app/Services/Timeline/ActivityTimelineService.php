<?php

namespace App\Services\Timeline;

use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\Deal;
use App\Models\Opportunity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ActivityTimelineService
{
    /**
     * @return Collection<int, array{occurred_at: Carbon, title: string, detail: string}>
     */
    public function forContact(Contact $contact): Collection
    {
        $contact = $contact->load([
            'opportunities.tasks',
            'opportunities.deal',
            'contactInteractions',
        ]);

        $opportunityIds = $contact->opportunities->pluck('id');
        $opportunityTitles = $contact->opportunities
            ->mapWithKeys(fn (Opportunity $opportunity): array => [$opportunity->id => $opportunity->title]);

        $events = collect();

        foreach ($contact->contactInteractions as $interaction) {
            if (! $interaction instanceof ContactInteraction || ! $interaction->happened_at instanceof Carbon) {
                continue;
            }

            $events->push([
                'occurred_at' => $interaction->happened_at,
                'title' => $interaction->summary,
                'detail' => 'Gorusme kaydi',
            ]);
        }

        foreach ($contact->opportunities as $opportunity) {
            foreach ($opportunity->tasks as $task) {
                $events->push([
                    'occurred_at' => Carbon::parse($task->created_at),
                    'title' => 'Gorev olusturuldu',
                    'detail' => (string) $task->title,
                ]);
            }

            if ($opportunity->deal instanceof Deal) {
                $events->push([
                    'occurred_at' => $opportunity->deal->closed_at instanceof Carbon
                        ? $opportunity->deal->closed_at
                        : Carbon::parse($opportunity->deal->created_at),
                    'title' => 'Anlasma olusturuldu',
                    'detail' => $opportunity->title,
                ]);
            }
        }

        $events = $events->merge($this->stageChangeEvents($opportunityIds, $opportunityTitles));

        return $events
            ->sortByDesc(fn (array $event): int => $event['occurred_at']->getTimestamp())
            ->values();
    }

    /**
     * @return Collection<int, array{occurred_at: Carbon, title: string, detail: string}>
     */
    public function forOpportunity(Opportunity $opportunity): Collection
    {
        $opportunity = $opportunity->load([
            'contact.contactInteractions',
            'tasks',
            'deal',
        ]);

        $events = collect();

        if ($opportunity->contact !== null) {
            foreach ($opportunity->contact->contactInteractions as $interaction) {
                if (! $interaction instanceof ContactInteraction || ! $interaction->happened_at instanceof Carbon) {
                    continue;
                }

                $events->push([
                    'occurred_at' => $interaction->happened_at,
                    'title' => $interaction->summary,
                    'detail' => 'Gorusme kaydi',
                ]);
            }
        }

        foreach ($opportunity->tasks as $task) {
            $events->push([
                'occurred_at' => Carbon::parse($task->created_at),
                'title' => 'Gorev olusturuldu',
                'detail' => (string) $task->title,
            ]);
        }

        if ($opportunity->deal instanceof Deal) {
            $events->push([
                'occurred_at' => $opportunity->deal->closed_at instanceof Carbon
                    ? $opportunity->deal->closed_at
                    : Carbon::parse($opportunity->deal->created_at),
                'title' => 'Anlasma olusturuldu',
                'detail' => $opportunity->title,
            ]);
        }

        $events = $events->merge($this->stageChangeEvents(
            collect([$opportunity->id]),
            collect([$opportunity->id => $opportunity->title]),
        ));

        return $events
            ->sortByDesc(fn (array $event): int => $event['occurred_at']->getTimestamp())
            ->values();
    }

    /**
     * @param  Collection<int, int>  $opportunityIds
     * @param  Collection<int, string>  $opportunityTitles
     * @return Collection<int, array{occurred_at: Carbon, title: string, detail: string}>
     */
    private function stageChangeEvents(Collection $opportunityIds, Collection $opportunityTitles): Collection
    {
        if ($opportunityIds->isEmpty()) {
            return collect();
        }

        return AuditLog::query()
            ->where('entity_type', Opportunity::class)
            ->where('action', 'opportunity_stage_changed')
            ->whereIn('entity_id', $opportunityIds)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (AuditLog $log) use ($opportunityTitles): array {
                $payload = is_array($log->payload) ? $log->payload : [];
                $from = (string) ($payload['from_stage'] ?? 'Belirsiz');
                $to = (string) ($payload['to_stage'] ?? 'Belirsiz');
                $opportunityTitle = (string) ($opportunityTitles->get($log->entity_id) ?? 'Firsat');

                return [
                    'occurred_at' => $log->created_at instanceof Carbon
                        ? $log->created_at
                        : Carbon::parse($log->created_at),
                    'title' => 'Asama degisimi',
                    'detail' => "{$from} -> {$to} · {$opportunityTitle}",
                ];
            });
    }
}
