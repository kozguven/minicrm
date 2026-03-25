<?php

namespace Tests\Feature\Ui;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandPaletteUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_command_palette_trigger_in_header(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $this->actingAs($user)
            ->get('/today')
            ->assertOk()
            ->assertSeeText('Ctrl/Cmd + K')
            ->assertSee('data-command-palette', false);
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
