<?php

namespace Tests\Feature\Contacts;

use App\Models\Contact;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ContactInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_companies_view_permission_can_see_contact_interactions_on_contact_detail(): void
    {
        Carbon::setTestNow('2026-03-26 10:00:00');

        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $contact = Contact::factory()->create([
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
        ]);

        $this->actingAs($user)
            ->post('/contact-interactions', [
                'contact_id' => $contact->id,
                'channel' => 'call',
                'happened_at' => '2026-03-26 09:30:00',
                'summary' => 'Teklif guncellemesi paylasildi',
                'notes' => 'Musteri revize teklif bekliyor.',
                'follow_up_due_at' => '2026-03-27 11:00:00',
            ])
            ->assertRedirect("/contacts/{$contact->id}");

        $this->actingAs($user)
            ->get("/contacts/{$contact->id}")
            ->assertOk()
            ->assertSeeText('Görüşme Geçmişi')
            ->assertSeeText('Teklif guncellemesi paylasildi')
            ->assertSeeText('Musteri revize teklif bekliyor.')
            ->assertSeeText('Takip tarihi');
    }

    public function test_user_with_companies_create_permission_can_add_contact_interaction(): void
    {
        Carbon::setTestNow('2026-03-26 10:00:00');

        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $contact = Contact::factory()->create();

        $this->actingAs($user)
            ->post('/contact-interactions', [
                'contact_id' => $contact->id,
                'channel' => 'meeting',
                'happened_at' => '2026-03-26 09:30:00',
                'summary' => 'Yuz yuze durum degerlendirmesi',
                'notes' => 'Bir sonraki adim maliyet onayi.',
                'follow_up_due_at' => '2026-03-28 10:00:00',
            ])
            ->assertRedirect("/contacts/{$contact->id}");

        $this->assertDatabaseHas('contact_interactions', [
            'contact_id' => $contact->id,
            'user_id' => $user->id,
            'channel' => 'meeting',
            'summary' => 'Yuz yuze durum degerlendirmesi',
            'follow_up_completed_at' => null,
        ]);
    }

    public function test_user_without_companies_create_permission_cannot_add_contact_interaction(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $contact = Contact::factory()->create();

        $this->actingAs($user)
            ->post('/contact-interactions', [
                'contact_id' => $contact->id,
                'channel' => 'call',
                'happened_at' => now()->format('Y-m-d H:i:s'),
                'summary' => 'Yetkisiz not',
            ])
            ->assertForbidden();
    }

    public function test_user_with_companies_create_permission_can_toggle_follow_up_completion(): void
    {
        Carbon::setTestNow('2026-03-26 10:00:00');

        $user = $this->userWithPermissions(['companies.view', 'companies.create']);
        $contact = Contact::factory()->create();

        $this->actingAs($user)
            ->post('/contact-interactions', [
                'contact_id' => $contact->id,
                'channel' => 'call',
                'happened_at' => '2026-03-26 09:30:00',
                'summary' => 'Takip edilmesi gereken gorusme',
                'follow_up_due_at' => '2026-03-27 10:00:00',
            ]);

        $interactionId = (int) \DB::table('contact_interactions')
            ->where('summary', 'Takip edilmesi gereken gorusme')
            ->value('id');

        $this->from("/contacts/{$contact->id}")
            ->actingAs($user)
            ->patch("/contact-interactions/{$interactionId}/toggle-follow-up")
            ->assertRedirect("/contacts/{$contact->id}");

        $this->assertDatabaseHas('contact_interactions', [
            'id' => $interactionId,
        ]);
        $this->assertNotNull(\DB::table('contact_interactions')->where('id', $interactionId)->value('follow_up_completed_at'));
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    private function userWithPermissions(array $permissionKeys): User
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $permissionIds = collect($permissionKeys)
            ->map(fn (string $permissionKey) => Permission::factory()->create(['key' => $permissionKey])->id)
            ->all();

        $role->permissions()->sync($permissionIds);
        $user->roles()->sync([$role->id]);

        return $user;
    }
}
