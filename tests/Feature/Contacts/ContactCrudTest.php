<?php

namespace Tests\Feature\Contacts;

use App\Http\Requests\StoreContactRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_company_or_contact_flows(): void
    {
        $company = Company::factory()->create();
        $contact = Contact::factory()->create();

        $this->get('/companies')->assertRedirect('/login');
        $this->get('/companies/create')->assertRedirect('/login');
        $this->post('/companies', [
            'name' => 'Mini CRM Ltd.',
            'website' => 'https://minicrm.test',
        ])->assertRedirect('/login');
        $this->get("/companies/{$company->id}/edit")->assertRedirect('/login');
        $this->patch("/companies/{$company->id}", [
            'name' => 'Mini CRM Updated',
            'website' => 'https://updated.test',
        ])->assertRedirect('/login');

        $this->get('/contacts')->assertRedirect('/login');
        $this->get('/contacts/create')->assertRedirect('/login');
        $this->post('/contacts', [
            'company_id' => $company->id,
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
        ])->assertRedirect('/login');
        $this->get("/contacts/{$contact->id}/edit")->assertRedirect('/login');
        $this->patch("/contacts/{$contact->id}", [
            'company_id' => $company->id,
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
        ])->assertRedirect('/login');
    }

    public function test_authenticated_user_without_crm_permissions_cannot_access_company_or_contact_flows(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $contact = Contact::factory()->create();

        $this->actingAs($user)->get('/companies')->assertForbidden();
        $this->actingAs($user)->get('/companies/create')->assertForbidden();
        $this->actingAs($user)->post('/companies', [
            'name' => 'Yetkisiz Sirket',
            'website' => 'https://yetkisiz.test',
        ])->assertForbidden();
        $this->actingAs($user)->get("/companies/{$company->id}/edit")->assertForbidden();
        $this->actingAs($user)->patch("/companies/{$company->id}", [
            'name' => 'Yetkisiz Sirket',
            'website' => 'https://yetkisiz.test',
        ])->assertForbidden();

        $this->actingAs($user)->get('/contacts')->assertForbidden();
        $this->actingAs($user)->get('/contacts/create')->assertForbidden();
        $this->actingAs($user)->post('/contacts', [
            'company_id' => $company->id,
            'first_name' => 'Yetkisiz',
            'last_name' => 'Kullanici',
        ])->assertForbidden();
        $this->actingAs($user)->get("/contacts/{$contact->id}/edit")->assertForbidden();
        $this->actingAs($user)->patch("/contacts/{$contact->id}", [
            'company_id' => $company->id,
            'first_name' => 'Yetkisiz',
            'last_name' => 'Kullanici',
        ])->assertForbidden();
    }

    public function test_user_with_companies_view_permission_can_view_companies_index(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $company = Company::factory()->create(['name' => 'Acme A.S.']);

        $this->actingAs($user)
            ->get('/companies')
            ->assertOk()
            ->assertSee('class="panel panel--xl"', false)
            ->assertSeeText('Şirketler')
            ->assertSeeText('Acme A.S.')
            ->assertSeeText('Yeni Şirket');
    }

    public function test_companies_index_supports_search_by_name_or_website(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        Company::factory()->create([
            'name' => 'Atlas Lojistik',
            'website' => 'https://atlas.test',
        ]);
        Company::factory()->create([
            'name' => 'Pera Yazilim',
            'website' => 'https://pera.test',
        ]);

        $this->actingAs($user)
            ->get('/companies?q=atlas')
            ->assertOk()
            ->assertSeeText('Atlas Lojistik')
            ->assertDontSeeText('Pera Yazilim');
    }

    public function test_user_with_only_companies_view_permission_cannot_open_company_create_screen(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $this->actingAs($user)
            ->get('/companies/create')
            ->assertForbidden();
    }

    public function test_user_with_companies_create_permission_can_open_company_create_screen(): void
    {
        $user = $this->userWithPermissions(['companies.create']);

        $this->actingAs($user)
            ->get('/companies/create')
            ->assertOk()
            ->assertSeeText('Yeni Şirket');
    }

    public function test_user_with_companies_create_permission_can_create_company(): void
    {
        $user = $this->userWithPermissions(['companies.create']);

        $this->actingAs($user)
            ->post('/companies', [
                'name' => 'Mini CRM Ltd.',
                'website' => 'https://minicrm.test',
            ])
            ->assertRedirect('/companies');

        $this->assertDatabaseHas('companies', [
            'name' => 'Mini CRM Ltd.',
            'website' => 'https://minicrm.test',
        ]);
    }

    public function test_user_with_companies_create_permission_can_open_company_edit_screen_and_update_company(): void
    {
        $user = $this->userWithPermissions(['companies.create']);
        $company = Company::factory()->create([
            'name' => 'Eski Isim',
            'website' => 'https://eski.test',
        ]);

        $this->actingAs($user)
            ->get("/companies/{$company->id}/edit")
            ->assertOk()
            ->assertSeeText('Şirketi Düzenle')
            ->assertSee('value="Eski Isim"', false);

        $this->actingAs($user)
            ->patch("/companies/{$company->id}", [
                'name' => 'Yeni Isim',
                'website' => 'https://yeni.test',
            ])
            ->assertRedirect('/companies');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Yeni Isim',
            'website' => 'https://yeni.test',
        ]);
    }

    public function test_user_with_companies_view_permission_can_view_contacts_index(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $company = Company::factory()->create(['name' => 'Acme A.S.']);
        Contact::factory()->create([
            'company_id' => $company->id,
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
            'email' => 'ayse@example.com',
        ]);

        $this->actingAs($user)
            ->get('/contacts')
            ->assertOk()
            ->assertSee('class="panel panel--xl"', false)
            ->assertSeeText('Kişiler')
            ->assertSeeText('Ayse Yilmaz')
            ->assertSeeText('Acme A.S.')
            ->assertSeeText('Yeni Kişi');
    }

    public function test_contacts_index_supports_search_by_contact_or_company_fields(): void
    {
        $user = $this->userWithPermissions(['companies.view']);
        $atlas = Company::factory()->create(['name' => 'Atlas Lojistik']);
        $pera = Company::factory()->create(['name' => 'Pera Yazilim']);

        Contact::factory()->create([
            'company_id' => $atlas->id,
            'first_name' => 'Ayse',
            'last_name' => 'Yildiz',
            'email' => 'ayse@atlas.test',
            'phone' => '+90 555 111 22 33',
        ]);
        Contact::factory()->create([
            'company_id' => $pera->id,
            'first_name' => 'Mert',
            'last_name' => 'Kaya',
            'email' => 'mert@pera.test',
            'phone' => '+90 555 444 55 66',
        ]);

        $this->actingAs($user)
            ->get('/contacts?q=atlas')
            ->assertOk()
            ->assertSeeText('Ayse Yildiz')
            ->assertDontSeeText('Mert Kaya');
    }

    public function test_user_with_only_companies_view_permission_cannot_open_contact_create_screen(): void
    {
        $user = $this->userWithPermissions(['companies.view']);

        $this->actingAs($user)
            ->get('/contacts/create')
            ->assertForbidden();
    }

    public function test_user_with_companies_create_permission_can_open_contact_create_screen(): void
    {
        $user = $this->userWithPermissions(['companies.create']);
        Company::factory()->create(['name' => 'Acme A.S.']);

        $this->actingAs($user)
            ->get('/contacts/create')
            ->assertOk()
            ->assertSeeText('Yeni Kişi')
            ->assertSeeText('Acme A.S.');
    }

    public function test_user_with_companies_create_permission_can_create_contact_under_company(): void
    {
        $user = $this->userWithPermissions(['companies.create']);
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->post('/contacts', [
                'company_id' => $company->id,
                'first_name' => 'Ayse',
                'last_name' => 'Yilmaz',
                'email' => 'ayse@example.com',
                'phone' => '+90 555 111 22 33',
            ])
            ->assertRedirect('/contacts');

        $this->assertDatabaseHas('contacts', [
            'company_id' => $company->id,
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
            'email' => 'ayse@example.com',
        ]);
    }

    public function test_user_with_companies_create_permission_can_open_contact_edit_screen_and_update_contact(): void
    {
        $user = $this->userWithPermissions(['companies.create']);
        $company = Company::factory()->create(['name' => 'Atlas']);
        $newCompany = Company::factory()->create(['name' => 'Pera']);
        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'first_name' => 'Eski',
            'last_name' => 'Kisi',
            'email' => 'eski@example.com',
        ]);

        $this->actingAs($user)
            ->get("/contacts/{$contact->id}/edit")
            ->assertOk()
            ->assertSeeText('Kişiyi Düzenle')
            ->assertSeeText('Atlas')
            ->assertSeeText('Pera');

        $this->actingAs($user)
            ->patch("/contacts/{$contact->id}", [
                'company_id' => $newCompany->id,
                'first_name' => 'Yeni',
                'last_name' => 'Kisi',
                'email' => 'yeni@example.com',
                'phone' => '+90 555 111 11 11',
            ])
            ->assertRedirect('/contacts');

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'company_id' => $newCompany->id,
            'first_name' => 'Yeni',
            'last_name' => 'Kisi',
            'email' => 'yeni@example.com',
            'phone' => '+90 555 111 11 11',
        ]);
    }

    public function test_contact_create_validation_returns_clear_turkish_messages(): void
    {
        $user = $this->userWithPermissions(['companies.create', 'companies.view']);

        $response = $this->from('/contacts/create')
            ->actingAs($user)
            ->post('/contacts', [
                'company_id' => 999999,
                'first_name' => '',
                'last_name' => '',
                'email' => 'gecersiz',
                'phone' => '',
            ]);

        $response->assertRedirect('/contacts/create');
        $response->assertSessionHasErrors([
            'company_id' => 'Lutfen gecerli bir sirket secin.',
            'first_name' => 'Ad alani zorunludur.',
            'last_name' => 'Soyad alani zorunludur.',
            'email' => 'E-posta gecerli bir e-posta adresi olmalidir.',
        ]);
    }

    public function test_contact_create_validation_covers_remaining_contact_rules_in_turkish(): void
    {
        $user = $this->userWithPermissions(['companies.create', 'companies.view']);

        $response = $this->from('/contacts/create')
            ->actingAs($user)
            ->post('/contacts', [
                'company_id' => 'abc',
                'first_name' => ['Ayse'],
                'last_name' => str_repeat('Y', 256),
                'email' => 'gecersiz',
                'phone' => str_repeat('5', 256),
            ]);

        $response->assertRedirect('/contacts/create');
        $response->assertSessionHasErrors([
            'company_id' => 'Sirket secimi sayi olmalidir.',
            'first_name' => 'Ad metin olmalidir.',
            'last_name' => 'Soyad en fazla 255 karakter olabilir.',
            'email' => 'E-posta gecerli bir e-posta adresi olmalidir.',
            'phone' => 'Telefon en fazla 255 karakter olabilir.',
        ]);
    }

    public function test_contact_request_declares_complete_turkish_message_and_attribute_coverage(): void
    {
        $request = new StoreContactRequest;

        $this->assertSame([
            'company_id.required' => 'Lutfen bir sirket secin.',
            'company_id.integer' => 'Sirket secimi sayi olmalidir.',
            'company_id.exists' => 'Lutfen gecerli bir sirket secin.',
            'owner_user_id.exists' => 'Lutfen gecerli bir sorumlu secin.',
            'required' => ':attribute alani zorunludur.',
            'string' => ':attribute metin olmalidir.',
            'max.string' => ':attribute en fazla :max karakter olabilir.',
            'email' => ':attribute gecerli bir e-posta adresi olmalidir.',
            'date' => ':attribute gecerli bir tarih olmalidir.',
            'in' => ':attribute degeri gecersiz.',
        ], $request->messages());

        $this->assertSame([
            'company_id' => 'Sirket',
            'owner_user_id' => 'Sorumlu',
            'first_name' => 'Ad',
            'last_name' => 'Soyad',
            'email' => 'E-posta',
            'phone' => 'Telefon',
            'lead_source' => 'Lead kaynagi',
            'lead_status' => 'Lead durumu',
            'priority' => 'Oncelik',
            'last_contacted_at' => 'Son temas tarihi',
        ], $request->attributes());
    }

    public function test_company_create_view_shows_all_validation_errors(): void
    {
        $user = $this->userWithPermissions(['companies.create', 'companies.view']);

        $this->followingRedirects()
            ->actingAs($user)
            ->from('/companies/create')
            ->post('/companies', [
                'name' => '',
                'website' => 'gecersiz-url',
            ])
            ->assertOk()
            ->assertSeeText('name alanı zorunludur.')
            ->assertSeeText('website geçerli bir URL olmalıdır.');
    }

    public function test_contact_create_view_shows_all_validation_errors(): void
    {
        $user = $this->userWithPermissions(['companies.create', 'companies.view']);

        $response = $this->from('/contacts/create')
            ->actingAs($user)
            ->post('/contacts', [
                'company_id' => '',
                'first_name' => '',
                'last_name' => '',
                'email' => 'gecersiz',
            ])
            ->assertRedirect('/contacts/create');

        $response->assertSessionHasErrors(['company_id', 'first_name', 'last_name', 'email']);

        $this->followingRedirects()
            ->actingAs($user)
            ->from('/contacts/create')
            ->post('/contacts', [
                'company_id' => '',
                'first_name' => '',
                'last_name' => '',
                'email' => 'gecersiz',
            ])
            ->assertOk()
            ->assertSeeText('Lutfen bir sirket secin.')
            ->assertSeeText('Ad alani zorunludur.')
            ->assertSeeText('Soyad alani zorunludur.')
            ->assertSeeText('E-posta gecerli bir e-posta adresi olmalidir.');
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
