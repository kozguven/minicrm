<?php

namespace Tests\Feature\Permissions;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_role_and_assign_action_permissions(): void
    {
        Permission::factory()->createMany([
            ['key' => 'companies.view'],
            ['key' => 'companies.create'],
            ['key' => 'opportunities.edit'],
            ['key' => 'deals.export'],
        ]);

        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post('/roles', [
            'name' => 'Satis',
            'permissions' => [
                'companies.view',
                'companies.create',
                'opportunities.edit',
                'deals.export',
            ],
        ]);

        $response->assertRedirect('/roles');
        $this->assertDatabaseHas('roles', ['name' => 'Satis']);

        $role = Role::query()->where('name', 'Satis')->firstOrFail();

        $this->assertSame(
            ['companies.create', 'companies.view', 'deals.export', 'opportunities.edit'],
            $role->permissions()->orderBy('key')->pluck('key')->all(),
        );
    }

    public function test_non_admin_cannot_create_roles(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/roles', ['name' => 'Yetkisiz'])
            ->assertForbidden();
    }

    public function test_admin_can_update_role_and_sync_selected_permissions(): void
    {
        Permission::factory()->createMany([
            ['key' => 'companies.view'],
            ['key' => 'companies.create'],
            ['key' => 'opportunities.edit'],
            ['key' => 'deals.export'],
        ]);

        $admin = $this->adminUser();
        $role = Role::factory()->create(['name' => 'Operasyon']);

        $role->permissions()->attach(
            Permission::query()->whereIn('key', ['companies.view', 'opportunities.edit'])->pluck('id')
        );

        $response = $this->actingAs($admin)->put("/roles/{$role->id}", [
            'name' => 'Operasyon Lideri',
            'permissions' => ['companies.create', 'deals.export'],
        ]);

        $response->assertRedirect('/roles');

        $role->refresh();

        $this->assertSame('Operasyon Lideri', $role->name);
        $this->assertSame(
            ['companies.create', 'deals.export'],
            $role->permissions()->orderBy('key')->pluck('key')->all(),
        );
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $adminRole = Role::factory()->create(['name' => 'Admin']);

        $user->roles()->attach($adminRole);

        return $user;
    }
}
