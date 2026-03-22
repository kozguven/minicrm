<?php

namespace Tests\Feature\Permissions;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_policy_allows_view_when_permission_is_granted_via_role(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['key' => 'companies.view']);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->assertTrue(Gate::forUser($user)->allows('view', $company));
    }

    public function test_company_policy_denies_view_without_matching_permission(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $this->assertFalse(Gate::forUser($user)->allows('view', $company));
    }
}
