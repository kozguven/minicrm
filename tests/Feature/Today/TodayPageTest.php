<?php

namespace Tests\Feature\Today;

use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TodayPageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/today')->assertRedirect('/login');
    }

    public function test_today_page_displays_priority_sections_and_items_in_order(): void
    {
        Carbon::setTestNow('2026-03-23 10:00:00');

        $user = $this->userWithPermissions(['companies.view']);
        $stage = OpportunityStage::factory()->create(['name' => 'Teklif', 'position' => 1]);

        $contact = Contact::factory()->create([
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
            'phone' => '+90 555 222 33 44',
        ]);
        Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Bugun Kapanacak Firsat',
            'expected_close_date' => '2026-03-23',
        ]);

        $criticalOpportunity = Opportunity::factory()->create([
            'contact_id' => Contact::factory()->create([
                'first_name' => 'Mert',
                'last_name' => 'Can',
            ])->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Kacmak Uzere Olan Teklif',
            'expected_close_date' => '2026-03-22',
        ]);

        $overdueTask = CrmTask::factory()->create([
            'title' => 'Teklif dosyasini guncelle',
            'opportunity_id' => $criticalOpportunity->id,
            'due_at' => Carbon::parse('2026-03-22 09:00:00'),
            'completed_at' => null,
        ]);

        $this->actingAs($user)
            ->get('/today')
            ->assertOk()
            ->assertSeeText('Günüm')
            ->assertSeeInOrder([
                'Aranacak Kişiler',
                'Kritik Fırsatlar',
                'Geciken Görevler',
            ])
            ->assertSeeText('Ayse Yilmaz')
            ->assertSeeText('Bugun Kapanacak Firsat')
            ->assertSeeText('Kacmak Uzere Olan Teklif')
            ->assertSeeText('Teklif dosyasini guncelle');
    }

    public function test_authenticated_user_without_crm_view_permission_can_still_access_today_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/today')
            ->assertOk()
            ->assertSeeText('CRM verilerini görmek için yetki gerekli')
            ->assertDontSeeText('Aranacak Kişiler')
            ->assertDontSeeText('Kritik Fırsatlar')
            ->assertDontSeeText('Geciken Görevler');
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
