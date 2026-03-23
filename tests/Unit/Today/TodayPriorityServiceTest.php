<?php

namespace Tests\Unit\Today;

use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Today\TodayPriorityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TodayPriorityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_prioritizes_calls_then_critical_opportunities_then_overdue_tasks(): void
    {
        Carbon::setTestNow('2026-03-23 10:00:00');

        $user = $this->userWithPermissions(['companies.view']);
        $stage = OpportunityStage::factory()->create(['name' => 'Teklif', 'position' => 1]);

        $callContact = Contact::factory()->create([
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
            'phone' => '+90 555 111 22 33',
        ]);
        Opportunity::factory()->create([
            'contact_id' => $callContact->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Bugun Aranacak Firsat',
            'expected_close_date' => '2026-03-23',
        ]);

        $criticalOpportunity = Opportunity::factory()->create([
            'contact_id' => Contact::factory()->create([
                'first_name' => 'Mehmet',
                'last_name' => 'Demir',
            ])->id,
            'opportunity_stage_id' => $stage->id,
            'title' => 'Kritik Yenileme',
            'expected_close_date' => '2026-03-22',
        ]);

        $overdueTask = CrmTask::factory()->create([
            'title' => 'Sozlesme revizyonunu gonder',
            'opportunity_id' => Opportunity::factory()->create([
                'opportunity_stage_id' => $stage->id,
                'expected_close_date' => '2026-03-30',
            ])->id,
            'due_at' => Carbon::parse('2026-03-22 15:00:00'),
            'completed_at' => null,
        ]);

        $sections = app(TodayPriorityService::class)->buildFor($user);

        $this->assertSame(
            ['call', 'critical_opportunity', 'overdue_task'],
            array_column($sections, 'type')
        );
        $this->assertSame([$callContact->id], $sections[0]['items']->pluck('id')->all());
        $this->assertSame([$criticalOpportunity->id], $sections[1]['items']->pluck('id')->all());
        $this->assertSame([$overdueTask->id], $sections[2]['items']->pluck('id')->all());
    }

    public function test_returns_empty_sections_for_user_without_view_permission(): void
    {
        Carbon::setTestNow('2026-03-23 10:00:00');

        $user = User::factory()->create();

        $sections = app(TodayPriorityService::class)->buildFor($user);

        $this->assertSame(
            ['call', 'critical_opportunity', 'overdue_task'],
            array_column($sections, 'type')
        );
        $this->assertSame([0, 0, 0], array_map(
            fn (array $section) => $section['items']->count(),
            $sections
        ));
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
