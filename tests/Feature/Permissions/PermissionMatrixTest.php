<?php

namespace Tests\Feature\Permissions;

use App\Models\Company;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
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

    public function test_company_policy_maps_view_any_and_create_to_permission_keys(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $viewPermission = Permission::factory()->create(['key' => 'companies.view']);
        $createPermission = Permission::factory()->create(['key' => 'companies.create']);

        $role->permissions()->attach([$viewPermission->id, $createPermission->id]);
        $user->roles()->attach($role);

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Company::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', Company::class));
    }

    public function test_contact_policy_reuses_company_permission_keys_for_view_any_and_create(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $viewPermission = Permission::factory()->create(['key' => 'companies.view']);
        $createPermission = Permission::factory()->create(['key' => 'companies.create']);

        $role->permissions()->attach([$viewPermission->id, $createPermission->id]);
        $user->roles()->attach($role);

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Contact::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', Contact::class));
    }

    public function test_opportunity_policy_maps_listing_creation_and_stage_updates_to_expected_permission_keys(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $opportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => OpportunityStage::factory()->create()->id,
        ]);
        $viewPermission = Permission::factory()->create(['key' => 'companies.view']);
        $createPermission = Permission::factory()->create(['key' => 'companies.create']);
        $editPermission = Permission::factory()->create(['key' => 'opportunities.edit']);

        $role->permissions()->attach([$viewPermission->id, $createPermission->id, $editPermission->id]);
        $user->roles()->attach($role);

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Opportunity::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', Opportunity::class));
        $this->assertTrue(Gate::forUser($user)->allows('update', $opportunity));
    }

    public function test_crm_task_policy_reuses_company_permission_keys_for_listing_and_creation(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $viewPermission = Permission::factory()->create(['key' => 'companies.view']);
        $createPermission = Permission::factory()->create(['key' => 'companies.create']);

        $role->permissions()->attach([$viewPermission->id, $createPermission->id]);
        $user->roles()->attach($role);

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', CrmTask::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', CrmTask::class));
    }
}
