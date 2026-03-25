<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmJsonEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_returns_grouped_json_results(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $stage = OpportunityStage::factory()->create(['name' => 'Teklif', 'position' => 1]);
        $company = Company::factory()->create(['name' => 'Zenith Lojistik']);
        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'first_name' => 'Aylin',
            'last_name' => 'Demir',
        ]);
        $opportunity = Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Zenith Yenileme',
        ]);
        CrmTask::factory()->create([
            'opportunity_id' => $opportunity->id,
            'title' => 'Zenith takip',
        ]);

        $this->actingAs($user)
            ->getJson('/search/global?q=zenith')
            ->assertOk()
            ->assertJsonPath('query', 'zenith')
            ->assertJsonPath('counts.companies', 1)
            ->assertJsonPath('counts.contacts', 1)
            ->assertJsonPath('counts.opportunities', 1)
            ->assertJsonPath('counts.tasks', 1)
            ->assertJsonPath('results.companies.0.name', 'Zenith Lojistik')
            ->assertJsonPath('results.contacts.0.full_name', 'Aylin Demir')
            ->assertJsonPath('results.opportunities.0.title', 'Zenith Yenileme')
            ->assertJsonPath('results.tasks.0.title', 'Zenith takip');
    }

    public function test_task_bulk_endpoint_returns_json_summary(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $first = CrmTask::factory()->create(['completed_at' => null]);
        $second = CrmTask::factory()->create(['completed_at' => null]);

        $this->actingAs($user)
            ->patchJson('/tasks/bulk', [
                'task_ids' => [$first->id, $second->id],
                'action' => 'complete',
            ])
            ->assertOk()
            ->assertJsonPath('action', 'complete')
            ->assertJsonPath('updated_count', 2)
            ->assertJsonPath('message', 'Secili gorevler tamamlandi.');
    }

    public function test_opportunity_bulk_stage_endpoint_returns_json_summary(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'opportunities.edit']);
        $targetStage = OpportunityStage::factory()->create(['name' => 'Muzakere', 'position' => 2]);
        $first = Opportunity::factory()->create();
        $second = Opportunity::factory()->create();

        $this->actingAs($user)
            ->patchJson('/opportunities/bulk-stage', [
                'opportunity_ids' => [$first->id, $second->id],
                'opportunity_stage_id' => $targetStage->id,
            ])
            ->assertOk()
            ->assertJsonPath('stage_id', $targetStage->id)
            ->assertJsonPath('updated_count', 2)
            ->assertJsonPath('message', 'Secili firsatlarin asamasi guncellendi.');
    }

    public function test_pipeline_and_forecast_endpoints_return_json_payloads(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $stage = OpportunityStage::factory()->create(['name' => 'Teklif', 'position' => 1]);
        $contact = Contact::factory()->create();
        Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Teklif Firsati',
            'value' => 10000,
            'probability' => 80,
            'health_status' => 'commit',
        ]);

        $this->actingAs($user)
            ->getJson('/reports/pipeline')
            ->assertOk()
            ->assertJsonPath('stages.0.name', 'Teklif')
            ->assertJsonPath('stages.0.opportunities_count', 1);

        $this->actingAs($user)
            ->getJson('/reports/forecast')
            ->assertOk()
            ->assertJsonPath('open_opportunities', 1)
            ->assertJsonPath('commit_forecast', 10000)
            ->assertJsonPath('best_case_forecast', 8000);
    }

    public function test_user_without_view_permission_cannot_access_json_crm_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/search/global?q=test')->assertForbidden();
        $this->actingAs($user)->getJson('/reports/pipeline')->assertForbidden();
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
