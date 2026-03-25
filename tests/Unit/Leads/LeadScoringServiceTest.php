<?php

namespace Tests\Unit\Leads;

use App\Models\Contact;
use App\Services\Leads\LeadScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_scores_high_for_hot_high_priority_lead(): void
    {
        $contact = Contact::factory()->create([
            'lead_source' => 'referral',
            'lead_status' => 'qualified',
            'priority' => 'high',
        ]);

        $score = app(LeadScoringService::class)->score($contact);

        $this->assertGreaterThanOrEqual(70, $score);
    }

    public function test_scores_low_for_cold_low_priority_lead(): void
    {
        $contact = Contact::factory()->create([
            'lead_source' => 'other',
            'lead_status' => 'new',
            'priority' => 'low',
        ]);

        $score = app(LeadScoringService::class)->score($contact);

        $this->assertLessThanOrEqual(40, $score);
    }
}
