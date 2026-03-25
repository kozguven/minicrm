<?php

namespace Tests\Feature\Search;

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

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_view_permission_can_search_across_crm_entities(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $company = Company::factory()->create(['name' => 'Zenith Lojistik']);
        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'first_name' => 'Aylin',
            'last_name' => 'Demir',
        ]);

        $stage = OpportunityStage::factory()->create(['name' => 'Teklif']);
        $opportunity = Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Zenith Yillik Kontrat',
        ]);

        CrmTask::factory()->create([
            'opportunity_id' => $opportunity->id,
            'title' => 'Zenith takip aramasi',
        ]);

        $this->actingAs($user)
            ->get('/search/global?q=zenith')
            ->assertOk()
            ->assertSeeText('Global Arama')
            ->assertSeeText('Zenith Lojistik')
            ->assertSeeText('Aylin Demir')
            ->assertSeeText('Zenith Yillik Kontrat')
            ->assertSeeText('Zenith takip aramasi');
    }

    public function test_user_without_view_permission_cannot_access_global_search(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/search/global?q=test')
            ->assertForbidden();
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
