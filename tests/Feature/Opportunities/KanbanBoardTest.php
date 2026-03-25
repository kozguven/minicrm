<?php

namespace Tests\Feature\Opportunities;

use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanbanBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_view_permission_can_open_kanban_board(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $stage = OpportunityStage::factory()->create(['name' => 'Teklif']);
        Opportunity::factory()->create([
            'opportunity_stage_id' => $stage->id,
            'title' => 'Kanban firsati',
        ]);

        $this->actingAs($user)
            ->get('/opportunities/kanban')
            ->assertOk()
            ->assertSeeText('Pipeline Kanban')
            ->assertSeeText('Teklif')
            ->assertSeeText('Kanban firsati');
    }

    public function test_kanban_board_renders_drag_drop_markers_for_stage_move(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'opportunities.edit']);

        $stage = OpportunityStage::factory()->create(['name' => 'Gorusme']);
        $opportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $stage->id,
            'title' => 'Surükle Birak Firsat',
        ]);

        $this->actingAs($user)
            ->get('/opportunities/kanban')
            ->assertOk()
            ->assertSee('data-kanban-board', false)
            ->assertSee("data-stage-id=\"{$stage->id}\"", false)
            ->assertSee("data-opportunity-id=\"{$opportunity->id}\"", false)
            ->assertSee('kanban-card--draggable', false);
    }

    public function test_stage_move_from_kanban_flow_writes_audit_log_when_user_is_authorized(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'opportunities.edit']);
        $fromStage = OpportunityStage::factory()->create(['name' => 'Ilk', 'position' => 1]);
        $toStage = OpportunityStage::factory()->create(['name' => 'Muzakere', 'position' => 2]);
        $opportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $fromStage->id,
        ]);

        $this->actingAs($user)
            ->patch("/opportunities/{$opportunity->id}/stage", [
                'opportunity_stage_id' => $toStage->id,
            ])
            ->assertRedirect('/opportunities');

        $this->assertDatabaseHas('opportunities', [
            'id' => $opportunity->id,
            'opportunity_stage_id' => $toStage->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'entity_type' => Opportunity::class,
            'entity_id' => $opportunity->id,
            'action' => 'opportunity_stage_changed',
        ]);
    }

    public function test_stage_move_is_blocked_without_edit_permission_and_no_audit_log_is_written(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $fromStage = OpportunityStage::factory()->create(['name' => 'Ilk', 'position' => 1]);
        $toStage = OpportunityStage::factory()->create(['name' => 'Muzakere', 'position' => 2]);
        $opportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $fromStage->id,
        ]);

        $this->actingAs($user)
            ->patch("/opportunities/{$opportunity->id}/stage", [
                'opportunity_stage_id' => $toStage->id,
            ])
            ->assertForbidden();

        $this->assertSame($fromStage->id, $opportunity->fresh()->opportunity_stage_id);

        $this->assertDatabaseMissing('audit_logs', [
            'entity_type' => Opportunity::class,
            'entity_id' => $opportunity->id,
            'action' => 'opportunity_stage_changed',
        ]);
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
