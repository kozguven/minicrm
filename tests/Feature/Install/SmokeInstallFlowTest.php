<?php

namespace Tests\Feature\Install;

use App\Models\User;
use Tests\TestCase;

class SmokeInstallFlowTest extends TestCase
{
    public function test_fresh_install_with_seed_shows_today_page_data(): void
    {
        $this->artisan('migrate:fresh', ['--seed' => true])->assertSuccessful();

        $user = User::query()
            ->where('email', 'admin@minicrm.local')
            ->firstOrFail();

        $this->actingAs($user)
            ->get('/today')
            ->assertOk()
            ->assertSeeText('Aranacak Kişiler')
            ->assertSeeText('Demo Insaat')
            ->assertSeeText('Yillik Bakim Yenilemesi');
    }
}
