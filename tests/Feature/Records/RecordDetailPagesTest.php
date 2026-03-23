<?php

namespace Tests\Feature\Records;

use App\Models\Company;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordDetailPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_record_detail_pages(): void
    {
        $company = Company::factory()->create();
        $contact = Contact::factory()->create();
        $opportunity = Opportunity::factory()->create();
        $task = CrmTask::factory()->create();
        $deal = Deal::factory()->create();

        $this->get("/companies/{$company->id}")->assertRedirect('/login');
        $this->get("/contacts/{$contact->id}")->assertRedirect('/login');
        $this->get("/opportunities/{$opportunity->id}")->assertRedirect('/login');
        $this->get("/tasks/{$task->id}")->assertRedirect('/login');
        $this->get("/deals/{$deal->id}")->assertRedirect('/login');
    }

    public function test_user_without_crm_view_permission_cannot_access_record_detail_pages(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $contact = Contact::factory()->create();
        $opportunity = Opportunity::factory()->create();
        $task = CrmTask::factory()->create();
        $deal = Deal::factory()->create();

        $this->actingAs($user)->get("/companies/{$company->id}")->assertForbidden();
        $this->actingAs($user)->get("/contacts/{$contact->id}")->assertForbidden();
        $this->actingAs($user)->get("/opportunities/{$opportunity->id}")->assertForbidden();
        $this->actingAs($user)->get("/tasks/{$task->id}")->assertForbidden();
        $this->actingAs($user)->get("/deals/{$deal->id}")->assertForbidden();
    }

    public function test_user_with_companies_view_permission_can_view_record_detail_pages(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $company = Company::factory()->create([
            'name' => 'Atlas Lojistik',
            'website' => 'https://atlas.test',
        ]);
        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'first_name' => 'Ayse',
            'last_name' => 'Yildiz',
            'email' => 'ayse@atlas.test',
        ]);
        $stage = OpportunityStage::factory()->create([
            'name' => 'Teklif',
        ]);
        $opportunity = Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Atlas Yenileme Paketi',
            'value' => 15000,
        ]);
        $task = CrmTask::factory()->create([
            'opportunity_id' => $opportunity->id,
            'title' => 'Teklifi guncelle',
            'completed_at' => null,
        ]);
        $deal = Deal::factory()->create([
            'opportunity_id' => $opportunity->id,
            'amount' => 15000,
        ]);

        $this->actingAs($user)
            ->get("/companies/{$company->id}")
            ->assertOk()
            ->assertSeeText('Şirket Detayı')
            ->assertSeeText('Atlas Lojistik')
            ->assertSeeText('Ayse Yildiz');

        $this->actingAs($user)
            ->get("/contacts/{$contact->id}")
            ->assertOk()
            ->assertSeeText('Kişi Detayı')
            ->assertSeeText('Ayse Yildiz')
            ->assertSeeText('Atlas Yenileme Paketi');

        $this->actingAs($user)
            ->get("/opportunities/{$opportunity->id}")
            ->assertOk()
            ->assertSeeText('Fırsat Detayı')
            ->assertSeeText('Atlas Yenileme Paketi')
            ->assertSeeText('Teklifi guncelle');

        $this->actingAs($user)
            ->get("/tasks/{$task->id}")
            ->assertOk()
            ->assertSeeText('Görev Detayı')
            ->assertSeeText('Teklifi guncelle')
            ->assertSeeText('Atlas Yenileme Paketi');

        $this->actingAs($user)
            ->get("/deals/{$deal->id}")
            ->assertOk()
            ->assertSeeText('Anlaşma Detayı')
            ->assertSeeText('Atlas Yenileme Paketi')
            ->assertSeeText('Ayse Yildiz');
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
