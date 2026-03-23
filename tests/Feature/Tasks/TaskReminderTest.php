<?php

namespace Tests\Feature\Tasks;

use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_task_flows(): void
    {
        $this->get('/tasks')->assertRedirect('/login');
        $this->get('/tasks/create')->assertRedirect('/login');
        $this->post('/tasks', [
            'opportunity_id' => Opportunity::factory()->create()->id,
            'title' => 'Mini CRM follow-up',
            'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertRedirect('/login');
    }

    public function test_authenticated_user_without_matching_permissions_cannot_access_task_flows(): void
    {
        $user = User::factory()->create();
        $opportunity = Opportunity::factory()->create();

        $this->actingAs($user)->get('/tasks')->assertForbidden();
        $this->actingAs($user)->get('/tasks/create')->assertForbidden();
        $this->actingAs($user)->post('/tasks', [
            'opportunity_id' => $opportunity->id,
            'title' => 'Yetkisiz gorev',
            'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertForbidden();
    }

    public function test_overdue_tasks_are_listed_on_task_index(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $overdueTask = CrmTask::factory()->create([
            'title' => 'Musteriyi ara',
            'due_at' => now()->subDay(),
            'completed_at' => null,
        ]);

        $this->actingAs($user)
            ->get('/tasks')
            ->assertOk()
            ->assertSeeText('Görevler')
            ->assertSeeText('Musteriyi ara')
            ->assertSeeText('Gecikmiş')
            ->assertSeeText($overdueTask->opportunity->title)
            ->assertDontSeeText('Yeni Görev');
    }

    public function test_user_with_companies_view_and_create_permissions_sees_task_create_cta_on_index(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);

        $this->actingAs($user)
            ->get('/tasks')
            ->assertOk()
            ->assertSeeText('Yeni Görev');
    }

    public function test_user_with_only_companies_view_permission_cannot_open_task_create_screen(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $opportunity = Opportunity::factory()->create();

        $this->actingAs($user)
            ->get('/tasks/create')
            ->assertForbidden();

        $this->actingAs($user)
            ->post('/tasks', [
                'opportunity_id' => $opportunity->id,
                'title' => 'Sadece goruntuleme yetkisi',
                'due_at' => '2026-03-30 10:30:00',
            ])
            ->assertForbidden();
    }

    public function test_user_with_only_companies_create_permission_cannot_open_task_create_screen_or_create_task(): void
    {
        $user = $this->userWithPermissions(['companies.create']);
        $opportunity = Opportunity::factory()->create(['title' => 'Mini CRM Retainer']);

        $this->actingAs($user)
            ->get('/tasks/create')
            ->assertForbidden();

        $this->actingAs($user)
            ->post('/tasks', [
                'opportunity_id' => $opportunity->id,
                'title' => 'Yetkisiz gorev olusturma',
                'due_at' => '2026-03-30 10:30:00',
            ])
            ->assertForbidden();
    }

    public function test_user_with_companies_view_and_create_permissions_can_open_task_create_screen(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $opportunity = Opportunity::factory()->create(['title' => 'Mini CRM Retainer']);

        $this->actingAs($user)
            ->get('/tasks/create')
            ->assertOk()
            ->assertSeeText('Yeni Görev')
            ->assertSeeText('Mini CRM Retainer')
            ->assertSeeText($opportunity->contact->first_name)
            ->assertSeeText('Vazgeç');
    }

    public function test_user_with_companies_view_and_create_permissions_can_create_task_and_see_success_message_on_index(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $opportunity = Opportunity::factory()->create();

        $this->followingRedirects()
            ->actingAs($user)
            ->post('/tasks', [
                'opportunity_id' => $opportunity->id,
                'title' => 'Teklif sunumunu hazirla',
                'due_at' => '2026-03-30 10:30:00',
            ])
            ->assertOk()
            ->assertSeeText('Gorev kaydedildi.')
            ->assertSeeText('Teklif sunumunu hazirla');

        $this->assertDatabaseHas('crm_tasks', [
            'opportunity_id' => $opportunity->id,
            'title' => 'Teklif sunumunu hazirla',
            'completed_at' => null,
        ]);
    }

    public function test_task_create_validation_returns_clear_turkish_messages(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);

        $response = $this->from('/tasks/create')
            ->actingAs($user)
            ->post('/tasks', [
                'opportunity_id' => 999999,
                'title' => '',
                'due_at' => 'gecersiz-tarih',
            ]);

        $response->assertRedirect('/tasks/create');
        $response->assertSessionHasErrors([
            'opportunity_id' => 'Lutfen gecerli bir firsat secin.',
            'title' => 'Gorev basligi alani zorunludur.',
            'due_at' => 'Termin gecerli bir tarih olmalidir.',
        ]);
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
