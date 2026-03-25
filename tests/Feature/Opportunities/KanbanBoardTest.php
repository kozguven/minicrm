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
