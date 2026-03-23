<?php

namespace Tests\Feature\Reports;

use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_shows_open_opportunities_and_weekly_closed_deals(): void
    {
        Carbon::setTestNow('2026-03-23 10:00:00');

        $user = $this->userWithPermissions(['companies.view']);
        $stage = OpportunityStage::factory()->create();

        $openOpportunities = Opportunity::factory()->count(3)->create([
            'opportunity_stage_id' => $stage->id,
            'expected_close_date' => '2026-03-30',
        ]);

        $closedOpportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $stage->id,
            'expected_close_date' => '2026-03-20',
        ]);

        Deal::factory()->create([
            'opportunity_id' => $closedOpportunity->id,
            'closed_at' => Carbon::parse('2026-03-23 09:00:00'),
        ]);

        Deal::factory()->create([
            'opportunity_id' => Opportunity::factory()->create([
                'opportunity_stage_id' => $stage->id,
                'expected_close_date' => '2026-03-18',
            ])->id,
            'closed_at' => Carbon::parse('2026-03-24 12:00:00'),
        ]);

        Deal::factory()->create([
            'opportunity_id' => Opportunity::factory()->create([
                'opportunity_stage_id' => $stage->id,
                'expected_close_date' => '2026-03-17',
            ])->id,
            'closed_at' => Carbon::parse('2026-03-15 12:00:00'),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeText('Açık Fırsatlar')
            ->assertSeeText((string) $openOpportunities->count())
            ->assertSeeText('Haftalık Kapanan Satış')
            ->assertSeeText('2');
    }

    public function test_dashboard_shows_permission_message_for_user_without_crm_view_access(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeText('CRM verilerini görmek için yetki gerekli')
            ->assertDontSeeText('Açık Fırsatlar')
            ->assertDontSeeText('Haftalık Kapanan Satış');
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
