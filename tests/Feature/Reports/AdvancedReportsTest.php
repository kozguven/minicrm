<?php

namespace Tests\Feature\Reports;

use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\CrmTask;
use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_funnel_report_shows_conversion_metrics(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $stage = OpportunityStage::factory()->create();

        $qualifiedA = Contact::factory()->create(['lead_status' => 'qualified']);
        $qualifiedB = Contact::factory()->create(['lead_status' => 'qualified']);
        Contact::factory()->create(['lead_status' => 'qualified']);
        Contact::factory()->create(['lead_status' => 'new']);
        Contact::factory()->create(['lead_status' => 'contacted']);

        Deal::factory()->create([
            'opportunity_id' => Opportunity::factory()->create([
                'contact_id' => $qualifiedA->id,
                'opportunity_stage_id' => $stage->id,
            ])->id,
        ]);
        Deal::factory()->create([
            'opportunity_id' => Opportunity::factory()->create([
                'contact_id' => $qualifiedB->id,
                'opportunity_stage_id' => $stage->id,
            ])->id,
        ]);

        $this->actingAs($user)
            ->get('/reports/funnel')
            ->assertOk()
            ->assertSeeText('Funnel Raporu')
            ->assertSeeText('Toplam Lead')
            ->assertSeeText('Qualified Lead')
            ->assertSeeText('Won Anlasma')
            ->assertSeeText('Lead -> Qualified')
            ->assertSeeText('Qualified -> Won');
    }

    public function test_sales_cycle_report_shows_average_and_bottleneck_stage(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $stageA = OpportunityStage::factory()->create(['name' => 'Kesif']);
        $stageB = OpportunityStage::factory()->create(['name' => 'Teklif']);

        $opportunityA = Opportunity::factory()->create([
            'opportunity_stage_id' => $stageA->id,
            'created_at' => '2026-03-01 10:00:00',
            'updated_at' => '2026-03-01 10:00:00',
        ]);
        $opportunityB = Opportunity::factory()->create([
            'opportunity_stage_id' => $stageB->id,
            'created_at' => '2026-03-02 10:00:00',
            'updated_at' => '2026-03-02 10:00:00',
        ]);

        Deal::factory()->create([
            'opportunity_id' => $opportunityA->id,
            'closed_at' => '2026-03-11 10:00:00',
            'created_at' => '2026-03-11 10:00:00',
            'updated_at' => '2026-03-11 10:00:00',
        ]);
        Deal::factory()->create([
            'opportunity_id' => $opportunityB->id,
            'closed_at' => '2026-03-07 10:00:00',
            'created_at' => '2026-03-07 10:00:00',
            'updated_at' => '2026-03-07 10:00:00',
        ]);

        Opportunity::factory()->create([
            'opportunity_stage_id' => $stageA->id,
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);

        $this->actingAs($user)
            ->get('/reports/sales-cycle')
            ->assertOk()
            ->assertSeeText('Satis Dongusu Raporu')
            ->assertSeeText('Ortalama Kapanis Suresi')
            ->assertSeeText('Darbogaz Asama')
            ->assertSeeText('Kesif');
    }

    public function test_performance_report_shows_user_task_and_follow_up_metrics(): void
    {
        $viewer = $this->userWithPermissions(['companies.view']);
        $owner = User::factory()->create(['name' => 'Ayse Yilmaz']);
        $stage = OpportunityStage::factory()->create();
        $contact = Contact::factory()->create(['owner_user_id' => $owner->id]);
        $opportunity = Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'owner_user_id' => $owner->id,
            'opportunity_stage_id' => $stage->id,
        ]);

        CrmTask::factory()->create([
            'opportunity_id' => $opportunity->id,
            'assigned_user_id' => $owner->id,
            'due_at' => now()->subDay(),
            'completed_at' => null,
        ]);
        CrmTask::factory()->create([
            'opportunity_id' => $opportunity->id,
            'assigned_user_id' => $owner->id,
            'due_at' => now()->addDay(),
            'completed_at' => null,
        ]);

        ContactInteraction::factory()->create([
            'contact_id' => $contact->id,
            'user_id' => $owner->id,
            'follow_up_due_at' => now()->subDay(),
            'follow_up_completed_at' => now()->subHours(2),
        ]);

        $this->actingAs($viewer)
            ->get('/reports/performance')
            ->assertOk()
            ->assertSeeText('Kullanici Performans Raporu')
            ->assertSeeText('Ayse Yilmaz')
            ->assertSeeText('Acik Gorev Yuku')
            ->assertSeeText('Gecikme Orani')
            ->assertSeeText('Takip Tamamlama Orani');
    }

    public function test_data_quality_report_shows_core_gaps(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $stage = OpportunityStage::factory()->create();

        Contact::factory()->create([
            'email' => null,
            'phone' => null,
            'owner_user_id' => null,
        ]);
        Opportunity::factory()->create([
            'opportunity_stage_id' => $stage->id,
            'owner_user_id' => null,
            'next_step' => null,
        ]);
        CrmTask::factory()->create([
            'assigned_user_id' => null,
        ]);

        $this->actingAs($user)
            ->get('/reports/data-quality')
            ->assertOk()
            ->assertSeeText('Veri Kalite Paneli')
            ->assertSeeText('Eksik E-posta')
            ->assertSeeText('Eksik Telefon')
            ->assertSeeText('Next-step Eksigi')
            ->assertSeeText('Atanmamis Kayitlar');
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
