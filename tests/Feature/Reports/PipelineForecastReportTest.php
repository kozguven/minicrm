<?php

namespace Tests\Feature\Reports;

use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineForecastReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_report_shows_stage_distribution(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $newStage = OpportunityStage::factory()->create(['name' => 'Yeni']);
        $offerStage = OpportunityStage::factory()->create(['name' => 'Teklif']);

        Opportunity::factory()->create(['opportunity_stage_id' => $newStage->id]);
        Opportunity::factory()->create(['opportunity_stage_id' => $offerStage->id]);
        Opportunity::factory()->create(['opportunity_stage_id' => $offerStage->id]);

        $this->actingAs($user)
            ->get('/reports/pipeline')
            ->assertOk()
            ->assertSeeText('Pipeline Raporu')
            ->assertSeeText('Yeni')
            ->assertSeeText('Teklif');
    }

    public function test_forecast_report_shows_commit_and_best_case_values(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $opportunity = Opportunity::factory()->create([
            'value' => 100000,
            'probability' => 65,
            'health_status' => 'commit',
        ]);

        Opportunity::factory()->create([
            'value' => 50000,
            'probability' => 30,
            'health_status' => 'risk',
        ]);

        Deal::factory()->create(['opportunity_id' => $opportunity->id]);

        $this->actingAs($user)
            ->get('/reports/forecast')
            ->assertOk()
            ->assertSeeText('Tahmin Paneli')
            ->assertSeeText('Commit Tahmin')
            ->assertSeeText('Best-case Tahmin');
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
