<?php

namespace Tests\Unit\Automation;

use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Services\Automation\SlaFollowUpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SlaFollowUpServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);

        parent::tearDown();
    }

    public function test_creates_follow_up_task_for_stale_contact_activity(): void
    {
        Carbon::setTestNow('2026-03-26 10:00:00');

        $contact = Contact::factory()->create();
        $opportunity = Opportunity::factory()->create(['contact_id' => $contact->id]);

        ContactInteraction::factory()->create([
            'contact_id' => $contact->id,
            'happened_at' => now()->subDays(4),
        ]);

        $created = app(SlaFollowUpService::class)->run();

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('crm_tasks', [
            'opportunity_id' => $opportunity->id,
            'task_type' => 'sla_follow_up',
            'title' => 'SLA takip gorevi',
        ]);
    }

    public function test_does_not_duplicate_open_sla_task_for_same_opportunity(): void
    {
        Carbon::setTestNow('2026-03-26 10:00:00');

        $contact = Contact::factory()->create();
        $opportunity = Opportunity::factory()->create(['contact_id' => $contact->id]);

        ContactInteraction::factory()->create([
            'contact_id' => $contact->id,
            'happened_at' => now()->subDays(7),
        ]);

        CrmTask::factory()->create([
            'opportunity_id' => $opportunity->id,
            'task_type' => 'sla_follow_up',
            'completed_at' => null,
        ]);

        $created = app(SlaFollowUpService::class)->run();

        $this->assertSame(0, $created);
    }
}
