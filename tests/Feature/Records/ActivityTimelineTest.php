<?php

namespace Tests\Feature\Records;

use App\Models\AuditLog;
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

class ActivityTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_detail_shows_unified_activity_timeline(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $contact = Contact::factory()->create();
        $oldStage = OpportunityStage::factory()->create(['name' => 'Yeni']);
        $newStage = OpportunityStage::factory()->create(['name' => 'Teklif']);

        $opportunity = Opportunity::factory()->create([
            'contact_id' => $contact->id,
            'opportunity_stage_id' => $newStage->id,
            'title' => 'Kurumsal Lisans Yenileme',
        ]);

        ContactInteraction::factory()->create([
            'contact_id' => $contact->id,
            'user_id' => $user->id,
            'summary' => 'Ilk gorusme yapildi',
            'happened_at' => '2026-03-24 09:00:00',
        ]);

        CrmTask::factory()->create([
            'opportunity_id' => $opportunity->id,
            'title' => 'Demo toplantisini planla',
            'created_at' => '2026-03-24 10:00:00',
            'updated_at' => '2026-03-24 10:00:00',
        ]);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'entity_type' => Opportunity::class,
            'entity_id' => $opportunity->id,
            'action' => 'opportunity_stage_changed',
            'payload' => [
                'from_stage' => $oldStage->name,
                'to_stage' => $newStage->name,
            ],
            'created_at' => '2026-03-24 11:00:00',
        ]);

        Deal::factory()->create([
            'opportunity_id' => $opportunity->id,
            'amount' => 25000,
            'closed_at' => '2026-03-24 12:00:00',
            'created_at' => '2026-03-24 12:00:00',
            'updated_at' => '2026-03-24 12:00:00',
        ]);

        $this->actingAs($user)
            ->get("/contacts/{$contact->id}")
            ->assertOk()
            ->assertSeeText('Aktivite Zaman Cizgisi')
            ->assertSeeText('Anlasma olusturuldu')
            ->assertSeeText('Asama degisimi')
            ->assertSeeText('Gorev olusturuldu')
            ->assertSeeText('Ilk gorusme yapildi');
    }

    public function test_opportunity_detail_shows_unified_activity_timeline(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $oldStage = OpportunityStage::factory()->create(['name' => 'Yeni']);
        $newStage = OpportunityStage::factory()->create(['name' => 'Teklif']);
        $opportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $newStage->id,
            'title' => 'Yillik Paket',
        ]);

        CrmTask::factory()->create([
            'opportunity_id' => $opportunity->id,
            'title' => 'Sozlesme revizyonu',
            'created_at' => '2026-03-24 08:30:00',
            'updated_at' => '2026-03-24 08:30:00',
        ]);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'entity_type' => Opportunity::class,
            'entity_id' => $opportunity->id,
            'action' => 'opportunity_stage_changed',
            'payload' => [
                'from_stage' => $oldStage->name,
                'to_stage' => $newStage->name,
            ],
            'created_at' => '2026-03-24 09:30:00',
        ]);

        Deal::factory()->create([
            'opportunity_id' => $opportunity->id,
            'amount' => 18000,
            'closed_at' => '2026-03-24 10:30:00',
            'created_at' => '2026-03-24 10:30:00',
            'updated_at' => '2026-03-24 10:30:00',
        ]);

        $this->actingAs($user)
            ->get("/opportunities/{$opportunity->id}")
            ->assertOk()
            ->assertSeeText('Aktivite Zaman Cizgisi')
            ->assertSeeText('Anlasma olusturuldu')
            ->assertSeeText('Asama degisimi')
            ->assertSeeText('Sozlesme revizyonu');
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
