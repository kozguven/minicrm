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

    public function test_non_admin_cannot_view_roles_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/roles')
            ->assertForbidden();
    }

    public function test_non_admin_cannot_view_role_create_screen(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/roles/create')
            ->assertForbidden();
    }

    public function test_non_admin_cannot_view_role_edit_screen(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->actingAs($user)
            ->get("/roles/{$role->id}/edit")
            ->assertForbidden();
    }

    public function test_non_admin_cannot_update_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Operasyon']);

        $this->actingAs($user)
            ->put("/roles/{$role->id}", [
                'name' => 'Degisti',
                'permissions' => [],
            ])
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

    public function test_admin_role_name_cannot_be_changed(): void
    {
        $admin = $this->adminUser();
        $adminRole = Role::query()->where('name', 'Admin')->firstOrFail();

        $response = $this->from("/roles/{$adminRole->id}/edit")
            ->actingAs($admin)
            ->put("/roles/{$adminRole->id}", [
                'name' => 'Super Admin',
                'permissions' => [],
            ]);

        $response->assertRedirect("/roles/{$adminRole->id}/edit");
        $response->assertSessionHasErrors(['name']);

        $this->assertDatabaseHas('roles', [
            'id' => $adminRole->id,
            'name' => 'Admin',
        ]);
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $adminRole = Role::factory()->create(['name' => 'Admin']);

        $user->roles()->attach($adminRole);

        return $user;
    }
}
