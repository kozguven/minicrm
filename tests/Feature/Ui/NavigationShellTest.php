<?php

namespace Tests\Feature\Ui;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_sees_global_navigation_links_on_today_page(): void
    {
        $admin = $this->adminUserWithPermissions(['companies.view']);

        $this->actingAs($admin)
            ->get('/today')
            ->assertOk()
            ->assertSee('data-nav-shell="global"', false)
            ->assertSeeText('Günüm')
            ->assertSeeText('Dashboard')
            ->assertSeeText('Şirketler')
            ->assertSeeText('Kişiler')
            ->assertSeeText('Fırsatlar')
            ->assertSeeText('Görevler')
            ->assertSeeText('Anlaşmalar')
            ->assertSeeText('Roller')
            ->assertSeeText('Takım');
    }

    public function test_navigation_marks_active_section_for_dashboard(): void
    {
        $admin = $this->adminUserWithPermissions(['companies.view']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder([
                'href="/dashboard"',
                'aria-current="page"',
            ], false)
            ->assertSee('href="/today"', false);
    }

    public function test_today_permission_state_uses_permission_panel_container(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/today')
            ->assertOk()
            ->assertSeeText('CRM verilerini görmek için yetki gerekli.')
            ->assertSee('class="permission-panel"', false);
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    private function adminUserWithPermissions(array $permissionKeys): User
    {
        $admin = User::factory()->create();
        $adminRole = Role::factory()->create(['name' => 'Admin']);

        $permissionIds = collect($permissionKeys)
            ->map(fn (string $permissionKey) => Permission::factory()->create(['key' => $permissionKey])->id)
            ->all();

        $adminRole->permissions()->sync($permissionIds);
        $admin->roles()->sync([$adminRole->id]);

        return $admin;
    }
}
