<?php

namespace Tests\Unit\Permissions;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Permissions\PermissionResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_permission_query_returns_expected_permission_keys(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $otherRole = Role::factory()->create();
        $viewPermission = Permission::factory()->create(['key' => 'companies.view']);
        $editPermission = Permission::factory()->create(['key' => 'companies.edit']);
        $unrelatedPermission = Permission::factory()->create(['key' => 'contacts.view']);

        $role->permissions()->attach([$viewPermission->id, $editPermission->id]);
        $otherRole->permissions()->attach($unrelatedPermission);
        $user->roles()->attach([$role->id, $otherRole->id]);

        $this->assertSame(
            ['companies.edit', 'companies.view', 'contacts.view'],
            $user->permissionsQuery()->orderBy('permissions.key')->pluck('permissions.key')->all(),
        );
    }

    public function test_user_has_permission_through_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['key' => 'companies.view']);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $resolver = app(PermissionResolver::class);

        $this->assertTrue($resolver->can($user, 'companies.view'));
    }

    public function test_user_without_linked_permission_is_denied(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->roles()->attach($role);

        $resolver = app(PermissionResolver::class);

        $this->assertFalse($resolver->can($user, 'companies.view'));
    }
}
