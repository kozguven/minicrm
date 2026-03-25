<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrmEnhancementSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_opportunities_and_tasks_include_new_crm_fields(): void
    {
        $this->assertTrue(Schema::hasColumns('contacts', [
            'owner_user_id',
            'lead_source',
            'lead_status',
            'priority',
            'last_contacted_at',
        ]));

        $this->assertTrue(Schema::hasColumns('opportunities', [
            'owner_user_id',
            'probability',
            'next_step',
            'next_step_due_at',
            'health_status',
        ]));

        $this->assertTrue(Schema::hasColumns('crm_tasks', [
            'assigned_user_id',
            'priority',
            'task_type',
        ]));
    }
}
