<?php

namespace Tests\Feature\Tasks;

use App\Models\CrmTask;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskBulkUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_task_permissions_can_bulk_complete_tasks(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);

        $first = CrmTask::factory()->create(['completed_at' => null]);
        $second = CrmTask::factory()->create(['completed_at' => null]);

        $this->actingAs($user)
            ->patch('/tasks/bulk', [
                'task_ids' => [$first->id, $second->id],
                'action' => 'complete',
            ])
            ->assertRedirect('/tasks')
            ->assertSessionHas('status');

        $this->assertNotNull($first->fresh()->completed_at);
        $this->assertNotNull($second->fresh()->completed_at);
    }

    public function test_bulk_update_rolls_back_when_payload_is_invalid(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);

        $task = CrmTask::factory()->create(['completed_at' => null]);

        $this->actingAs($user)
            ->patch('/tasks/bulk', [
                'task_ids' => [$task->id, 999999],
                'action' => 'complete',
            ])
            ->assertSessionHasErrors('task_ids.1');

        $this->assertNull($task->fresh()->completed_at);
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
