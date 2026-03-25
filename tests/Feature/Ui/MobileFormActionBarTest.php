<?php

namespace Tests\Feature\Ui;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileFormActionBarTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_create_form_uses_sticky_mobile_action_bar_class(): void
    {
        $user = $this->userWithPermissions(['companies.create']);

        $this->actingAs($user)
            ->get('/contacts/create')
            ->assertOk()
            ->assertSee('class="inline-actions form-actions"', false);
    }

    public function test_opportunity_create_form_uses_sticky_mobile_action_bar_class(): void
    {
        $user = $this->userWithPermissions(['companies.create']);

        $this->actingAs($user)
            ->get('/opportunities/create')
            ->assertOk()
            ->assertSee('class="inline-actions form-actions"', false);
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
