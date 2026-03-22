<?php

namespace Tests\Feature\Opportunities;

use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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

        $this->assertCount(1, $opportunity->tasks);
    }

    public function test_permissions_and_audit_logs_match_planned_schema(): void
    {
        $this->assertTrue(Schema::hasColumns('permissions', ['id', 'key', 'created_at', 'updated_at']));
        $this->assertFalse(Schema::hasColumn('permissions', 'name'));

        $this->assertTrue(Schema::hasColumns('audit_logs', [
            'id',
            'user_id',
            'entity_type',
            'entity_id',
            'action',
            'payload',
            'created_at',
        ]));
        $this->assertFalse(Schema::hasColumn('audit_logs', 'event'));
        $this->assertFalse(Schema::hasColumn('audit_logs', 'old_values'));
        $this->assertFalse(Schema::hasColumn('audit_logs', 'new_values'));
    }
}
