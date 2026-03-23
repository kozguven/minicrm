<?php

namespace Tests\Feature\Contacts;

use App\Http\Requests\StoreContactRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_companies_index_and_create_screen(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['name' => 'Acme A.S.']);

        $this->actingAs($user)
            ->get('/companies')
            ->assertOk()
            ->assertSeeText('Sirketler')
            ->assertSeeText('Acme A.S.')
            ->assertSeeText('Yeni Sirket');

        $this->actingAs($user)
            ->get('/companies/create')
            ->assertOk()
            ->assertSeeText('Yeni Sirket');
    }

    public function test_authenticated_user_can_create_company(): void
    {
        $user = User::factory()->create();

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

    public function test_authenticated_user_can_view_contacts_index_and_create_screen(): void
    {
        $user = User::factory()->create();
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
            ->assertSeeText('Kisiler')
            ->assertSeeText('Ayse Yilmaz')
            ->assertSeeText('Acme A.S.')
            ->assertSeeText('Yeni Kisi');

        $this->actingAs($user)
            ->get('/contacts/create')
            ->assertOk()
            ->assertSeeText('Yeni Kisi')
            ->assertSeeText('Acme A.S.');
    }

    public function test_authorized_user_can_create_contact_under_company(): void
    {
        $user = User::factory()->create();
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

    public function test_contact_create_validation_returns_clear_turkish_messages(): void
    {
        $user = User::factory()->create();

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
        $user = User::factory()->create();

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
        $request = new StoreContactRequest();

        $this->assertSame([
            'company_id.required' => 'Lutfen bir sirket secin.',
            'company_id.integer' => 'Sirket secimi sayi olmalidir.',
            'company_id.exists' => 'Lutfen gecerli bir sirket secin.',
            'required' => ':attribute alani zorunludur.',
            'string' => ':attribute metin olmalidir.',
            'max.string' => ':attribute en fazla :max karakter olabilir.',
            'email' => ':attribute gecerli bir e-posta adresi olmalidir.',
        ], $request->messages());

        $this->assertSame([
            'company_id' => 'Sirket',
            'first_name' => 'Ad',
            'last_name' => 'Soyad',
            'email' => 'E-posta',
            'phone' => 'Telefon',
        ], $request->attributes());
    }
}
