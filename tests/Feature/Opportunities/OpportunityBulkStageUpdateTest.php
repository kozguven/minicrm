<?php

namespace Tests\Feature\Opportunities;

use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityBulkStageUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_bulk_update_opportunity_stages(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create', 'opportunities.edit']);

        $targetStage = OpportunityStage::factory()->create(['name' => 'Muzakere']);
        $first = Opportunity::factory()->create();
        $second = Opportunity::factory()->create();

        $this->actingAs($user)
            ->patch('/opportunities/bulk-stage', [
                'opportunity_ids' => [$first->id, $second->id],
                'opportunity_stage_id' => $targetStage->id,
            ])
            ->assertRedirect('/opportunities')
            ->assertSessionHas('status');

        $this->assertSame($targetStage->id, $first->fresh()->opportunity_stage_id);
        $this->assertSame($targetStage->id, $second->fresh()->opportunity_stage_id);
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    private function userWithPermissions(array $permissionKeys): User
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $permissionIds = collect($permissionKeys)
            ->map(fn (string $permissionKey) => Permission::factory()->create(['key' => $permissionKey])->id);

        $role->permissions()->attach($permissionIds);
        $user->roles()->attach($role);

        return $user;
    }
}
