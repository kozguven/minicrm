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
