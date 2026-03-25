<?php

namespace Tests\Feature\Acceptance;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FirstTenMinutesFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);

        parent::tearDown();
    }

    public function test_today_page_contains_prioritized_sections_and_dashboard_shortcut(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $this->actingAs($user)
            ->get('/today')
            ->assertOk()
            ->assertSeeInOrder([
                'Aranacak Kişiler',
                'Kritik Fırsatlar',
                'Geciken Görevler',
            ])
            ->assertSeeText('Dashboard');
    }

    public function test_new_member_can_create_company_contact_opportunity_and_task_quickly(): void
    {
        Carbon::setTestNow('2026-03-23 10:00:00');

        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $stage = OpportunityStage::factory()->create(['name' => 'Yeni', 'position' => 1]);

        $this->actingAs($user)->post('/companies', [
            'name' => 'Demo A.S.',
            'website' => 'https://demoas.example.com',
        ])->assertRedirect('/companies');

        $company = Company::query()->where('name', 'Demo A.S.')->firstOrFail();

        $this->actingAs($user)->post('/contacts', [
            'company_id' => $company->id,
            'first_name' => 'Mert',
            'last_name' => 'Can',
            'email' => 'mert.can@demoas.example.com',
            'phone' => '+90 555 555 55 55',
        ])->assertRedirect('/contacts');

        $contact = Contact::query()->where('email', 'mert.can@demoas.example.com')->firstOrFail();

        $this->actingAs($user)->post('/opportunities', [
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Yillik Lisans',
            'value' => 96000,
            'expected_close_date' => '2026-03-23',
            'next_step' => 'Demo toplantisi planla',
            'next_step_due_at' => '2026-03-23 15:00:00',
        ])->assertRedirect('/opportunities');

        $opportunity = Opportunity::query()->where('title', 'Yillik Lisans')->firstOrFail();

        $this->assertDatabaseHas('opportunities', [
            'id' => $opportunity->id,
            'next_step' => 'Demo toplantisi planla',
        ]);

        $this->actingAs($user)->post('/tasks', [
            'opportunity_id' => $opportunity->id,
            'title' => 'Ilk aramayi yap',
            'due_at' => '2026-03-23 09:00:00',
        ])->assertRedirect('/tasks');

        $this->assertDatabaseHas('crm_tasks', ['title' => 'Ilk aramayi yap']);

        $this->actingAs($user)
            ->get('/today')
            ->assertOk()
            ->assertSeeText('Mert Can')
            ->assertSeeText('Yillik Lisans')
            ->assertSeeText('Ilk aramayi yap');
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
