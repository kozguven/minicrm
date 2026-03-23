<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Deal;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CrmDemoSeeder extends Seeder
{
    public function run(): void
    {
        $permissionKeys = [
            'companies.view',
            'companies.create',
            'opportunities.edit',
            'deals.export',
        ];

        foreach ($permissionKeys as $permissionKey) {
            Permission::query()->firstOrCreate(['key' => $permissionKey]);
        }

        $adminRole = Role::query()->firstOrCreate(['name' => 'Admin']);
        $salesRole = Role::query()->firstOrCreate(['name' => 'Satis']);

        $adminRole->permissions()->sync(
            Permission::query()->whereIn('key', $permissionKeys)->pluck('id')
        );
        $salesRole->permissions()->sync(
            Permission::query()
                ->whereIn('key', ['companies.view', 'companies.create', 'opportunities.edit'])
                ->pluck('id')
        );

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@minicrm.local'],
            ['name' => 'Mini CRM Admin', 'password' => 'secret123']
        );
        $salesUser = User::query()->firstOrCreate(
            ['email' => 'satis@minicrm.local'],
            ['name' => 'Satis Uzmani', 'password' => 'secret123']
        );

        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        $salesUser->roles()->syncWithoutDetaching([$salesRole->id]);

        $yeniStage = OpportunityStage::query()->firstOrCreate(
            ['name' => 'Yeni'],
            ['position' => 1]
        );
        $teklifStage = OpportunityStage::query()->firstOrCreate(
            ['name' => 'Teklif'],
            ['position' => 2]
        );
        $muzakereStage = OpportunityStage::query()->firstOrCreate(
            ['name' => 'Muzakere'],
            ['position' => 3]
        );

        $demoCompany = Company::query()->firstOrCreate(
            ['name' => 'Demo Insaat'],
            ['website' => 'https://demoinsaat.example.com']
        );
        $atlasCompany = Company::query()->firstOrCreate(
            ['name' => 'Atlas Lojistik'],
            ['website' => 'https://atlaslojistik.example.com']
        );

        $todayContact = Contact::query()->firstOrCreate(
            ['email' => 'mert.can@demoinsaat.example.com'],
            [
                'company_id' => $demoCompany->id,
                'first_name' => 'Mert',
                'last_name' => 'Can',
                'phone' => '+90 555 100 10 10',
            ]
        );
        $criticalContact = Contact::query()->firstOrCreate(
            ['email' => 'ayse.yildiz@atlaslojistik.example.com'],
            [
                'company_id' => $atlasCompany->id,
                'first_name' => 'Ayse',
                'last_name' => 'Yildiz',
                'phone' => '+90 555 200 20 20',
            ]
        );
        $dealContact = Contact::query()->firstOrCreate(
            ['email' => 'selim.arslan@atlaslojistik.example.com'],
            [
                'company_id' => $atlasCompany->id,
                'first_name' => 'Selim',
                'last_name' => 'Arslan',
                'phone' => '+90 555 300 30 30',
            ]
        );

        $today = Carbon::today();

        $todayOpportunity = Opportunity::query()->firstOrCreate(
            ['title' => 'Yillik Bakim Yenilemesi'],
            [
                'contact_id' => $todayContact->id,
                'opportunity_stage_id' => $teklifStage->id,
                'value' => 185000,
                'expected_close_date' => $today->toDateString(),
            ]
        );

        $criticalOpportunity = Opportunity::query()->firstOrCreate(
            ['title' => 'Geciken Cephe Teklifi'],
            [
                'contact_id' => $criticalContact->id,
                'opportunity_stage_id' => $muzakereStage->id,
                'value' => 246000,
                'expected_close_date' => $today->copy()->subDay()->toDateString(),
            ]
        );

        CrmTask::query()->firstOrCreate(
            ['title' => 'Gecikmis teklif aramasi'],
            [
                'opportunity_id' => $criticalOpportunity->id,
                'due_at' => $today->copy()->subDay()->setTime(15, 0),
                'completed_at' => null,
            ]
        );

        $wonOpportunity = Opportunity::query()->firstOrCreate(
            ['title' => 'Depo Otomasyon Paketi'],
            [
                'contact_id' => $dealContact->id,
                'opportunity_stage_id' => $yeniStage->id,
                'value' => 320000,
                'expected_close_date' => $today->copy()->subDays(2)->toDateString(),
            ]
        );

        Deal::query()->firstOrCreate(
            ['opportunity_id' => $wonOpportunity->id],
            [
                'amount' => 320000,
                'closed_at' => Carbon::now()->startOfWeek()->addDay(),
            ]
        );
    }
}
