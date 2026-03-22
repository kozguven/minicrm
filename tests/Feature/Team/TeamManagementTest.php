<?php

namespace Tests\Feature\Team;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_team_member_with_one_or_more_roles(): void
    {
        $admin = $this->adminUser();
        $salesRole = Role::factory()->create(['name' => 'Satis']);
        $supportRole = Role::factory()->create(['name' => 'Destek']);

        $response = $this->actingAs($admin)->post('/team', [
            'name' => 'Ayse Yilmaz',
            'email' => 'Ayse@Example.COM',
            'password' => 'secret123',
            'role_ids' => [$salesRole->id, $supportRole->id],
        ]);

        $response->assertRedirect('/team');
        $this->assertDatabaseHas('users', [
            'name' => 'Ayse Yilmaz',
            'email' => 'ayse@example.com',
        ]);

        $teamMember = User::query()->where('email', 'ayse@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('secret123', $teamMember->password));
        $this->assertSame(
            ['Destek', 'Satis'],
            $teamMember->roles()->orderBy('name')->pluck('name')->all(),
        );
    }

    public function test_team_member_appears_in_team_list(): void
    {
        $admin = $this->adminUser();
        $salesRole = Role::factory()->create(['name' => 'Satis']);
        $supportRole = Role::factory()->create(['name' => 'Destek']);
        $teamMember = User::factory()->create([
            'name' => 'Zeynep Kaya',
            'email' => 'zeynep@example.com',
        ]);

        $teamMember->roles()->attach([$salesRole->id, $supportRole->id]);

        $this->actingAs($admin)
            ->get('/team')
            ->assertOk()
            ->assertSeeText('Zeynep Kaya')
            ->assertSeeText('zeynep@example.com')
            ->assertSeeText('Destek, Satis');
    }

    public function test_non_admin_cannot_create_team_members(): void
    {
        $user = User::factory()->create();
        $salesRole = Role::factory()->create(['name' => 'Satis']);

        $this->actingAs($user)
            ->post('/team', [
                'name' => 'Yetkisiz Kullanici',
                'email' => 'yetkisiz@example.com',
                'password' => 'secret123',
                'role_ids' => [$salesRole->id],
            ])
            ->assertForbidden();
    }

    public function test_non_admin_cannot_view_team_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/team')
            ->assertForbidden();
    }

    public function test_non_admin_cannot_view_team_create_screen(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/team/create')
            ->assertForbidden();
    }

    public function test_duplicate_email_with_different_casing_is_rejected(): void
    {
        $admin = $this->adminUser();
        $salesRole = Role::factory()->create(['name' => 'Satis']);

        User::factory()->create([
            'email' => 'ayse@example.com',
        ]);

        $response = $this->from('/team/create')
            ->actingAs($admin)
            ->post('/team', [
                'name' => 'Ayse Yilmaz',
                'email' => 'AYSE@EXAMPLE.COM',
                'password' => 'secret123',
                'role_ids' => [$salesRole->id],
            ]);

        $response->assertRedirect('/team/create');
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(1, User::query()->where('email', 'ayse@example.com')->count());
    }

    public function test_invalid_stale_role_id_is_rejected(): void
    {
        $admin = $this->adminUser();

        $response = $this->from('/team/create')
            ->actingAs($admin)
            ->post('/team', [
                'name' => 'Ayse Yilmaz',
                'email' => 'ayse@example.com',
                'password' => 'secret123',
                'role_ids' => [999999],
            ]);

        $response->assertRedirect('/team/create');
        $response->assertSessionHasErrors(['role_ids.0']);
        $this->assertDatabaseMissing('users', ['email' => 'ayse@example.com']);
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $adminRole = Role::factory()->create(['name' => 'Admin']);

        $user->roles()->attach($adminRole);

        return $user;
    }
}
