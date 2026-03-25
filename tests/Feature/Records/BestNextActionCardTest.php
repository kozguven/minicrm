<?php

namespace Tests\Feature\Records;

use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BestNextActionCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_detail_shows_best_next_action_hint(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $contact = Contact::factory()->create();

        $this->actingAs($user)
            ->get("/contacts/{$contact->id}")
            ->assertOk()
            ->assertSeeText('Sonraki En Iyi Aksiyon');
    }

    public function test_opportunity_detail_shows_best_next_action_hint(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $stage = OpportunityStage::factory()->create();
        $opportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $stage->id,
            'next_step' => null,
        ]);

        $this->actingAs($user)
            ->get("/opportunities/{$opportunity->id}")
            ->assertOk()
            ->assertSeeText('Sonraki En Iyi Aksiyon');
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
