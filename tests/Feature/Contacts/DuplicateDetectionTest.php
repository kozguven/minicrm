<?php

namespace Tests\Feature\Contacts;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_store_warns_and_blocks_duplicate_name_or_website(): void
    {
        $user = $this->userWithPermissions(['companies.create']);

        Company::factory()->create([
            'name' => 'Atlas Lojistik',
            'website' => 'https://atlas.test',
        ]);

        $this->from('/companies/create')
            ->actingAs($user)
            ->post('/companies', [
                'name' => 'atlas lojistik',
                'website' => 'https://atlas.test',
            ])
            ->assertRedirect('/companies/create')
            ->assertSessionHasErrors('duplicate');

        $this->assertSame(1, Company::query()->count());
    }

    public function test_contact_store_warns_and_blocks_duplicate_email_or_phone(): void
    {
        $user = $this->userWithPermissions(['companies.create']);
        $company = Company::factory()->create();

        Contact::factory()->create([
            'company_id' => $company->id,
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
            'email' => 'ayse@example.com',
            'phone' => '+90 555 000 00 00',
        ]);

        $this->from('/contacts/create')
            ->actingAs($user)
            ->post('/contacts', [
                'company_id' => $company->id,
                'first_name' => 'Ayse',
                'last_name' => 'Yildiz',
                'email' => 'AYSE@example.com',
                'phone' => '+90 555 000 00 00',
            ])
            ->assertRedirect('/contacts/create')
            ->assertSessionHasErrors('duplicate');

        $this->assertSame(1, Contact::query()->count());
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
