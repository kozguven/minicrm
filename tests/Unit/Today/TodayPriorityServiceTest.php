<?php

namespace Tests\Unit\Today;

use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Services\Today\TodayPriorityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TodayPriorityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);

        parent::tearDown();
    }

    public function test_prioritizes_calls_then_critical_opportunities_then_overdue_tasks(): void
    {
        Carbon::setTestNow('2026-03-23 10:00:00');

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

        $sections = app(TodayPriorityService::class)->build();

        $this->assertSame(
            ['call', 'critical_opportunity', 'overdue_task', 'due_follow_up'],
            array_column($sections, 'type')
        );
        $this->assertSame(
            ['Aranacak Kişiler', 'Kritik Fırsatlar', 'Geciken Görevler', 'Takip Edilecek Görüşmeler'],
            array_column($sections, 'title')
        );
        $this->assertSame([$callContact->id], $sections[0]['items']->pluck('id')->all());
        $this->assertSame([$criticalOpportunity->id], $sections[1]['items']->pluck('id')->all());
        $this->assertSame([$overdueTask->id], $sections[2]['items']->pluck('id')->all());
        $this->assertCount(0, $sections[3]['items']);
    }

    public function test_excludes_contacts_without_phone_from_call_list(): void
    {
        Carbon::setTestNow('2026-03-23 10:00:00');

        $stage = OpportunityStage::factory()->create(['name' => 'Teklif', 'position' => 1]);
        $eligibleContact = Contact::factory()->create([
            'first_name' => 'Ayse',
            'last_name' => 'Yilmaz',
            'phone' => '+90 555 000 00 00',
        ]);
        $excludedContact = Contact::factory()->create([
            'first_name' => 'Mert',
            'last_name' => 'Can',
            'phone' => null,
        ]);

        Opportunity::factory()->create([
            'contact_id' => $eligibleContact->id,
            'opportunity_stage_id' => $stage->id,
            'expected_close_date' => '2026-03-23',
        ]);
        Opportunity::factory()->create([
            'contact_id' => $excludedContact->id,
            'opportunity_stage_id' => $stage->id,
            'expected_close_date' => '2026-03-23',
        ]);

        $sections = app(TodayPriorityService::class)->build();

        $this->assertSame([$eligibleContact->id], $sections[0]['items']->pluck('id')->all());
    }

    public function test_excludes_converted_opportunities_from_critical_section(): void
    {
        Carbon::setTestNow('2026-03-23 10:00:00');

        $stage = OpportunityStage::factory()->create(['name' => 'Teklif', 'position' => 1]);
        $eligibleOpportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $stage->id,
            'title' => 'Acil Yenileme',
            'expected_close_date' => '2026-03-22',
        ]);
        $convertedOpportunity = Opportunity::factory()->create([
            'opportunity_stage_id' => $stage->id,
            'title' => 'Anlasmaya Donusen Firsat',
            'expected_close_date' => '2026-03-21',
        ]);
        $convertedOpportunity->deal()->create([
            'title' => 'Donusen Anlasma',
            'amount' => 12500,
            'closed_at' => '2026-03-22',
        ]);

        $sections = app(TodayPriorityService::class)->build();

        $this->assertSame([$eligibleOpportunity->id], $sections[1]['items']->pluck('id')->all());
    }

    public function test_excludes_completed_tasks_from_overdue_section(): void
    {
        Carbon::setTestNow('2026-03-23 10:00:00');

        $stage = OpportunityStage::factory()->create(['name' => 'Teklif', 'position' => 1]);
        $eligibleTask = CrmTask::factory()->create([
            'title' => 'Geciken Arama',
            'opportunity_id' => Opportunity::factory()->create([
                'opportunity_stage_id' => $stage->id,
                'expected_close_date' => '2026-03-30',
            ])->id,
            'due_at' => Carbon::parse('2026-03-22 12:00:00'),
            'completed_at' => null,
        ]);
        CrmTask::factory()->create([
            'title' => 'Tamamlanmis Gecikmis Gorev',
            'opportunity_id' => Opportunity::factory()->create([
                'opportunity_stage_id' => $stage->id,
                'expected_close_date' => '2026-03-30',
            ])->id,
            'due_at' => Carbon::parse('2026-03-22 09:00:00'),
            'completed_at' => Carbon::parse('2026-03-22 10:00:00'),
        ]);

        $sections = app(TodayPriorityService::class)->build();

        $this->assertSame([$eligibleTask->id], $sections[2]['items']->pluck('id')->all());
    }
}
