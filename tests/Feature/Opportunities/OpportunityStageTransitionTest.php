<?php

namespace Tests\Feature\Opportunities;

use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityStageTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_opportunity_flows(): void
    {
        $opportunity = Opportunity::factory()->create();

        $this->get('/opportunities')->assertRedirect('/login');
        $this->get('/opportunities/create')->assertRedirect('/login');
        $this->post('/opportunities', [])->assertRedirect('/login');
        $this->patch("/opportunities/{$opportunity->id}/stage", [
            'opportunity_stage_id' => OpportunityStage::factory()->create()->id,
        ])->assertRedirect('/login');
    }

    public function test_authenticated_user_without_matching_permissions_cannot_access_opportunity_flows(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        $stage = OpportunityStage::factory()->create();
        $opportunity = Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
        ]);

        $this->actingAs($user)->get('/opportunities')->assertForbidden();
        $this->actingAs($user)->get('/opportunities/create')->assertForbidden();
        $this->actingAs($user)->post('/opportunities', [
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Mini CRM Retainer',
            'value' => '15000',
            'expected_close_date' => '2026-04-30',
        ])->assertForbidden();
        $this->actingAs($user)->patch("/opportunities/{$opportunity->id}/stage", [
            'opportunity_stage_id' => OpportunityStage::factory()->create()->id,
        ])->assertForbidden();
    }

    public function test_user_with_companies_view_permission_can_view_opportunities_index(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $stage = OpportunityStage::factory()->create(['name' => 'Gorusme', 'position' => 1]);
        $contact = Contact::factory()->create([
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
        ]);
        Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Mini CRM Retainer',
            'value' => 18000,
            'expected_close_date' => '2026-04-30',
        ]);

        $this->actingAs($user)
            ->get('/opportunities')
            ->assertOk()
            ->assertSeeText('Fırsatlar')
            ->assertSeeText('Mini CRM Retainer')
            ->assertSeeText('Ayse Yilmaz')
            ->assertSeeText('Gorusme')
            ->assertDontSeeText('Yeni Fırsat');
    }

    public function test_user_with_companies_view_and_create_permissions_sees_opportunity_create_cta_on_index(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);

        $this->actingAs($user)
            ->get('/opportunities')
            ->assertOk()
            ->assertSeeText('Yeni Fırsat');
    }

    public function test_user_with_only_companies_view_permission_cannot_open_opportunity_create_screen(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $this->actingAs($user)
            ->get('/opportunities/create')
            ->assertForbidden();
    }

    public function test_user_with_only_companies_view_permission_cannot_create_opportunity(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $contact = Contact::factory()->create();
        $stage = OpportunityStage::factory()->create();

        $this->actingAs($user)
            ->post('/opportunities', [
                'contact_id' => $contact->id,
                'opportunity_stage_id' => $stage->id,
                'title' => 'Yetkisiz Firsat',
                'value' => '9000',
                'expected_close_date' => '2026-04-30',
            ])
            ->assertForbidden();
    }

    public function test_user_with_companies_create_permission_can_open_opportunity_create_screen(): void
    {
        $user = $this->userWithPermissions(['companies.create']);
        $stage = OpportunityStage::factory()->create(['name' => 'Yeni']);
        $contact = Contact::factory()->create([
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
        ]);

        $this->actingAs($user)
            ->get('/opportunities/create')
            ->assertOk()
            ->assertSeeText('Yeni Fırsat')
            ->assertSeeText('Ayse Yilmaz')
            ->assertSeeText('Yeni');
    }

    public function test_user_with_companies_create_permission_can_create_opportunity(): void
    {
        $user = $this->userWithPermissions(['companies.create']);
        $contact = Contact::factory()->create();
        $stage = OpportunityStage::factory()->create(['name' => 'Yeni']);

        $this->followingRedirects()
            ->actingAs($user)
            ->post('/opportunities', [
                'contact_id' => $contact->id,
                'opportunity_stage_id' => $stage->id,
                'title' => 'Mini CRM Retainer',
                'value' => '15000.50',
                'expected_close_date' => '2026-04-30',
            ])
            ->assertOk()
            ->assertSeeText('Günüm');

        $this->assertDatabaseHas('opportunities', [
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Mini CRM Retainer',
            'expected_close_date' => '2026-04-30',
        ]);
    }

    public function test_user_can_move_opportunity_between_stages(): void
    {
        $user = $this->userWithPermissions(['opportunities.edit']);
        $opportunity = Opportunity::factory()->create();
        $nextStage = OpportunityStage::factory()->create(['name' => 'Teklif']);

        $this->followingRedirects()
            ->actingAs($user)
            ->patch("/opportunities/{$opportunity->id}/stage", [
                'opportunity_stage_id' => $nextStage->id,
            ])
            ->assertOk()
            ->assertSeeText('Günüm');

        $this->assertDatabaseHas('opportunities', [
            'id' => $opportunity->id,
            'opportunity_stage_id' => $nextStage->id,
        ]);
    }

    public function test_stage_validation_errors_are_shown_on_opportunities_index(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'opportunities.edit']);
        $opportunity = Opportunity::factory()->create();

        $response = $this->from('/opportunities')
            ->actingAs($user)
            ->patch("/opportunities/{$opportunity->id}/stage", [
                'opportunity_stage_id' => 999999,
            ]);

        $response->assertRedirect('/opportunities');
        $response->assertSessionHasErrors('opportunity_stage_id');

        $this->followingRedirects()
            ->from('/opportunities')
            ->actingAs($user)
            ->patch("/opportunities/{$opportunity->id}/stage", [
                'opportunity_stage_id' => 999999,
            ])
            ->assertOk()
            ->assertSeeText('Lutfen gecerli bir asama secin.');
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
