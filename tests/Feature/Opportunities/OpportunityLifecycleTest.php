<?php

namespace Tests\Feature\Opportunities;

use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class OpportunityLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_opportunity_relates_to_contact_stage_and_tasks(): void
    {
        $contact = Contact::factory()->create();
        $stage = OpportunityStage::factory()->create(['name' => 'Yeni']);
        $opportunity = Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
        ]);

        CrmTask::factory()->create(['opportunity_id' => $opportunity->id]);

        $this->assertTrue($opportunity->opportunityStage->is($stage));
        $this->assertFalse(method_exists($opportunity, 'stage'));
        $this->assertCount(1, $opportunity->tasks);
    }

    public function test_opportunity_has_a_single_deal_relation(): void
    {
        $opportunity = Opportunity::factory()->create();
        $deal = Deal::factory()->create(['opportunity_id' => $opportunity->id]);

        $this->assertTrue($opportunity->refresh()->deal->is($deal));

        $this->expectException(QueryException::class);

        Deal::factory()->create(['opportunity_id' => $opportunity->id]);
    }
}
