<?php

namespace Tests\Feature\Lists;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListErgonomicsTest extends TestCase
{
    use RefreshDatabase;

    public function test_companies_index_supports_sort_date_filter_and_pagination(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        Company::factory()->create([
            'name' => 'Alpha A.S.',
            'created_at' => '2026-03-01 10:00:00',
            'updated_at' => '2026-03-01 10:00:00',
        ]);

        Company::factory()->create([
            'name' => 'Zeta A.S.',
            'created_at' => '2026-03-20 10:00:00',
            'updated_at' => '2026-03-20 10:00:00',
        ]);

        Company::factory()->count(23)->create();

        $this->actingAs($user)
            ->get('/companies?sort=name_asc&created_from=2026-03-01&created_to=2026-03-31')
            ->assertOk()
            ->assertSeeText('Alpha A.S.')
            ->assertDontSeeText('Zeta A.S.')
            ->assertSee('page=2', false);

        $this->actingAs($user)
            ->get('/companies?sort=name_asc&created_from=2026-03-01&created_to=2026-03-31&page=2')
            ->assertOk()
            ->assertSeeText('Zeta A.S.');
    }

    public function test_contacts_index_supports_status_priority_and_last_contact_filters(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $company = Company::factory()->create();

        Contact::factory()->create([
            'company_id' => $company->id,
            'first_name' => 'Ayse',
            'last_name' => 'Yildiz',
            'lead_status' => 'qualified',
            'priority' => 'high',
            'last_contacted_at' => '2026-03-10 09:00:00',
        ]);

        Contact::factory()->create([
            'company_id' => $company->id,
            'first_name' => 'Mert',
            'last_name' => 'Kaya',
            'lead_status' => 'new',
            'priority' => 'low',
            'last_contacted_at' => '2026-03-01 09:00:00',
        ]);

        $this->actingAs($user)
            ->get('/contacts?lead_status=qualified&priority=high&last_contact_from=2026-03-05')
            ->assertOk()
            ->assertSeeText('Ayse Yildiz')
            ->assertDontSeeText('Mert Kaya');
    }

    public function test_deals_index_supports_date_range_and_sort_filter(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $olderDeal = Deal::factory()->create([
            'opportunity_id' => Opportunity::factory()->create([
                'title' => 'Subat Anlasmasi',
            ])->id,
            'amount' => 1000,
            'closed_at' => '2026-02-10 12:00:00',
        ]);

        Deal::factory()->create([
            'opportunity_id' => Opportunity::factory()->create([
                'title' => 'Nisan Anlasmasi',
            ])->id,
            'amount' => 3500,
            'closed_at' => '2026-04-12 12:00:00',
        ]);

        $this->actingAs($user)
            ->get('/deals?closed_from=2026-04-01&sort=amount_desc')
            ->assertOk()
            ->assertSeeText('Nisan Anlasmasi')
            ->assertDontSeeText((string) $olderDeal->opportunity?->title);
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
