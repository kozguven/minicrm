<?php

namespace Tests\Feature\Audit;

use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_deal_records_audit_log_with_actor_and_payload(): void
    {
        Permission::factory()->createMany([
            ['key' => 'companies.view'],
            ['key' => 'companies.create'],
        ]);

        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $stage = OpportunityStage::factory()->create();
        $opportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $stage->id,
        ]);

        $this->actingAs($user)
            ->post('/deals', [
                'opportunity_id' => $opportunity->id,
                'amount' => 17500,
                'closed_at' => '2026-03-23 10:00:00',
            ])
            ->assertRedirect('/deals');

        $deal = Deal::query()
            ->where('opportunity_id', $opportunity->id)
            ->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'entity_type' => Deal::class,
            'entity_id' => $deal->id,
            'action' => 'deal_created',
        ]);

        $payload = DB::table('audit_logs')
            ->where('entity_type', Deal::class)
            ->where('entity_id', $deal->id)
            ->value('payload');

        $this->assertEquals([
            'amount' => 17500,
            'opportunity_id' => $opportunity->id,
            'source' => 'create',
        ], json_decode((string) $payload, true));
    }

    public function test_role_permission_sync_records_audit_log(): void
    {
        Permission::factory()->createMany([
            ['key' => 'companies.view'],
            ['key' => 'companies.create'],
        ]);

        $admin = $this->adminUser();
        $role = Role::factory()->create(['name' => 'Operasyon']);

        $this->actingAs($admin)
            ->put("/roles/{$role->id}", [
                'name' => 'Operasyon',
                'permissions' => ['companies.create', 'companies.view'],
            ])
            ->assertRedirect('/roles');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'entity_type' => Role::class,
            'entity_id' => $role->id,
            'action' => 'permissions_synced',
        ]);

        $payload = DB::table('audit_logs')
            ->where('entity_type', Role::class)
            ->where('entity_id', $role->id)
            ->where('action', 'permissions_synced')
            ->value('payload');

        $this->assertEquals([
            'permission_keys' => ['companies.create', 'companies.view'],
        ], json_decode((string) $payload, true));
    }

    public function test_opportunity_stage_change_records_audit_log(): void
    {
        Permission::factory()->createMany([
            ['key' => 'opportunities.edit'],
        ]);

        $user = $this->userWithPermissions(['opportunities.edit']);
        $fromStage = OpportunityStage::factory()->create(['name' => 'Yeni']);
        $toStage = OpportunityStage::factory()->create(['name' => 'Teklif']);
        $opportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $fromStage->id,
        ]);

        $this->actingAs($user)
            ->patch("/opportunities/{$opportunity->id}/stage", [
                'opportunity_stage_id' => $toStage->id,
            ])
            ->assertRedirect('/today');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'entity_type' => Opportunity::class,
            'entity_id' => $opportunity->id,
            'action' => 'opportunity_stage_changed',
        ]);

        $payload = DB::table('audit_logs')
            ->where('entity_type', Opportunity::class)
            ->where('entity_id', $opportunity->id)
            ->where('action', 'opportunity_stage_changed')
            ->value('payload');

        $this->assertEquals([
            'from_stage' => 'Yeni',
            'to_stage' => 'Teklif',
        ], json_decode((string) $payload, true));
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    private function userWithPermissions(array $permissionKeys): User
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $permissionIds = Permission::query()
            ->whereIn('key', $permissionKeys)
            ->pluck('id');

        $role->permissions()->attach($permissionIds);
        $user->roles()->attach($role);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $adminRole = Role::factory()->create(['name' => 'Admin']);

        $user->roles()->attach($adminRole);

        return $user;
    }
}
