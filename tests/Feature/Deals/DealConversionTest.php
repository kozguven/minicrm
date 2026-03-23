<?php

namespace Tests\Feature\Deals;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_deal_flows(): void
    {
        $opportunity = Opportunity::factory()->create();

        $this->get('/deals')->assertRedirect('/login');
        $this->get('/deals/create')->assertRedirect('/login');
        $this->post('/deals', [])->assertRedirect('/login');
        $this->post("/opportunities/{$opportunity->id}/convert")->assertRedirect('/login');
    }

    public function test_authenticated_user_without_matching_permissions_cannot_access_deal_flows(): void
    {
        $user = User::factory()->create();
        $opportunity = Opportunity::factory()->create();

        $this->actingAs($user)->get('/deals')->assertForbidden();
        $this->actingAs($user)->get('/deals/create')->assertForbidden();
        $this->actingAs($user)->post('/deals', [
            'opportunity_id' => $opportunity->id,
            'amount' => '12500',
            'closed_at' => '2026-03-23 10:30:00',
        ])->assertForbidden();
        $this->actingAs($user)->post("/opportunities/{$opportunity->id}/convert")->assertForbidden();
    }

    public function test_user_with_companies_view_permission_can_view_deals_index(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $contact = Contact::factory()->create([
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
        ]);
        $opportunity = Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'title' => 'Mini CRM Yenileme',
            'value' => 24500,
        ]);
        Deal::factory()->create([
            'opportunity_id' => $opportunity->id,
            'amount' => 24500,
            'closed_at' => '2026-03-20 15:45:00',
        ]);

        $this->actingAs($user)
            ->get('/deals')
            ->assertOk()
            ->assertSeeText('Anlaşmalar')
            ->assertSeeText('Mini CRM Yenileme')
            ->assertSeeText('Ayse Yilmaz')
            ->assertDontSeeText('Yeni Anlaşma');
    }

    public function test_user_with_companies_view_and_create_permissions_can_open_deal_create_screen(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $opportunity = Opportunity::factory()->create([
            'title' => 'Kurulum Paketi',
        ]);

        $this->actingAs($user)
            ->get('/deals/create')
            ->assertOk()
            ->assertSeeText('Yeni Anlaşma')
            ->assertSeeText('Kurulum Paketi');
    }

    public function test_user_with_companies_view_and_create_permissions_can_create_deal_from_create_flow(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $opportunity = Opportunity::factory()->create([
            'title' => 'Manuel Kayit Firsati',
        ]);

        $this->followingRedirects()
            ->actingAs($user)
            ->post('/deals', [
                'opportunity_id' => $opportunity->id,
                'amount' => '18000.50',
                'closed_at' => '2026-03-23 11:15:00',
            ])
            ->assertOk()
            ->assertSeeText('Anlasma kaydedildi.');

        $this->assertDatabaseHas('deals', [
            'opportunity_id' => $opportunity->id,
            'amount' => '18000.50',
            'closed_at' => '2026-03-23 11:15:00',
        ]);
    }

    public function test_user_with_companies_view_and_create_permissions_can_convert_opportunity_into_deal(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $opportunity = Opportunity::factory()->create([
            'value' => 31500.75,
        ]);

        $this->followingRedirects()
            ->from('/opportunities')
            ->actingAs($user)
            ->post("/opportunities/{$opportunity->id}/convert")
            ->assertOk()
            ->assertSeeText('Firsat anlasmaya donusturuldu.');

        $this->assertDatabaseHas('deals', [
            'opportunity_id' => $opportunity->id,
            'amount' => '31500.75',
        ]);

        $this->assertNotNull(Deal::query()->where('opportunity_id', $opportunity->id)->value('closed_at'));
    }

    public function test_duplicate_opportunity_conversion_is_rejected_with_turkish_validation_message(): void
    {
        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $opportunity = Opportunity::factory()->create([
            'title' => 'Tekrarsiz Donusum',
            'value' => 42000,
        ]);

        $this->actingAs($user)->post("/opportunities/{$opportunity->id}/convert")->assertRedirect();

        $response = $this->from('/opportunities')
            ->actingAs($user)
            ->post("/opportunities/{$opportunity->id}/convert");

        $response->assertRedirect('/opportunities');
        $response->assertSessionHasErrors([
            'opportunity_id' => 'Bu firsat zaten bir anlasmaya donusturuldu.',
        ]);

        $this->assertSame(1, Deal::query()->where('opportunity_id', $opportunity->id)->count());
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
