<?php

namespace App\Services\Actions;

use App\Models\Contact;
use App\Models\Opportunity;
use Illuminate\Support\Carbon;

class BestNextActionService
{
    public function forContact(Contact $contact): string
    {
        $hasPendingFollowUp = $contact->contactInteractions
            ->contains(fn ($interaction): bool => $interaction->follow_up_due_at !== null && $interaction->follow_up_completed_at === null);

        if ($hasPendingFollowUp) {
            return 'Bekleyen takip gorusmesini bugun tamamlayin.';
        }

        $hasOpportunityWithoutNextStep = $contact->opportunities
            ->contains(fn (Opportunity $opportunity): bool => $opportunity->deal === null && blank($opportunity->next_step));

        if ($hasOpportunityWithoutNextStep) {
            return 'Acik firsatlar icin bir sonraki adimi tanimlayin.';
        }

        if ($contact->contactInteractions->isEmpty()) {
            return 'Ilk gorusmeyi planlayin ve takip tarihi atayin.';
        }

        return 'Son gorusmenin ozetini netlestirip yeni bir takip tarihi belirleyin.';
    }

    public function forOpportunity(Opportunity $opportunity): string
    {
        if ($opportunity->deal !== null) {
            return 'Anlasma sonrasi onboarding gorevini olusturun.';
        }

        if (blank($opportunity->next_step)) {
            return 'Bu firsat icin bir sonraki adimi mutlaka tanimlayin.';
        }

        if ($opportunity->next_step_due_at instanceof Carbon && $opportunity->next_step_due_at->isPast()) {
            return 'Geciken sonraki adimi bugun tamamlayin.';
        }

        $hasOpenTask = $opportunity->tasks
            ->contains(fn ($task): bool => $task->completed_at === null);

        if (! $hasOpenTask) {
            return 'Takip momentumunu korumak icin yeni bir gorev olusturun.';
        }

        return 'Asama ilerleyisini takip edip riski azaltmak icin kritik notlari guncelleyin.';
    }
}
