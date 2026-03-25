<?php

namespace App\Services\Leads;

use App\Models\Contact;

class LeadScoringService
{
    public function score(Contact $contact): int
    {
        $score = 25;

        $score += match ($contact->lead_source) {
            'referral' => 25,
            'event' => 15,
            'website' => 10,
            default => 0,
        };

        $score += match ($contact->lead_status) {
            'qualified' => 30,
            'contacted' => 15,
            'lost' => -20,
            default => 0,
        };

        $score += match ($contact->priority) {
            'high' => 20,
            'medium' => 10,
            default => 0,
        };

        if ($contact->email !== null) {
            $score += 5;
        }

        if ($contact->phone !== null) {
            $score += 10;
        }

        return max(0, min(100, $score));
    }
}
