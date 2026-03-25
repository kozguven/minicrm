<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Requests\UpdateOpportunityStageRequest;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use App\Models\User;
use App\Services\Actions\BestNextActionService;
use App\Services\Audit\AuditLogger;
use App\Services\Automation\StageTaskTemplateService;
use App\Services\Timeline\ActivityTimelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Opportunity::class);

        $search = trim((string) $request->query('q', ''));
        $stageFilter = $request->query('stage_id');
        $sort = (string) $request->query('sort', 'expected_close_desc');
        $allowedSorts = ['expected_close_desc', 'expected_close_asc', 'value_desc', 'value_asc', 'title_asc'];
        $normalizedSort = in_array($sort, $allowedSorts, true) ? $sort : 'expected_close_desc';

        $opportunities = Opportunity::query()
            ->with(['contact.company', 'opportunityStage', 'deal'])
            ->when($search !== '', function ($query) use ($search): void {
                $like = "%{$search}%";

                $query->where(function ($nestedQuery) use ($like): void {
                    $nestedQuery
                        ->where('title', 'like', $like)
                        ->orWhere('next_step', 'like', $like)
                        ->orWhereHas('contact', function ($contactQuery) use ($like): void {
                            $contactQuery
                                ->where('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like)
                                ->orWhereHas('company', fn ($companyQuery) => $companyQuery
                                    ->where('name', 'like', $like));
                        })
                        ->orWhereHas('opportunityStage', fn ($stageQuery) => $stageQuery
                            ->where('name', 'like', $like));
                });
            })
            ->when(is_numeric($stageFilter), fn ($query) => $query
                ->where('opportunity_stage_id', (int) $stageFilter));

        match ($normalizedSort) {
            'expected_close_asc' => $opportunities->orderBy('expected_close_date')->orderBy('title'),
            'value_desc' => $opportunities->orderByDesc('value')->orderBy('title'),
            'value_asc' => $opportunities->orderBy('value')->orderBy('title'),
            'title_asc' => $opportunities->orderBy('title'),
            default => $opportunities->orderByDesc('expected_close_date')->orderBy('title'),
        };

        return view('opportunities.index', [
            'opportunities' => $opportunities->paginate(20)->withQueryString(),
            'stages' => OpportunityStage::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(),
            'filters' => [
                'q' => $search,
                'stage_id' => is_numeric($stageFilter) ? (string) $stageFilter : '',
                'sort' => $normalizedSort,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Opportunity::class);

        return view('opportunities.create', [
            'contacts' => Contact::query()
                ->with('company')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
            'stages' => OpportunityStage::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function show(
        Opportunity $opportunity,
        BestNextActionService $bestNextActionService,
        ActivityTimelineService $activityTimelineService,
    ): View {
        $this->authorize('view', $opportunity);

        $opportunity = $opportunity->load([
            'contact.company',
            'opportunityStage',
            'deal',
            'tasks' => fn ($query) => $query
                ->orderByRaw('case when completed_at is null and due_at is not null and due_at < ? then 0 else 1 end', [now()])
                ->orderBy('due_at')
                ->orderBy('title'),
        ]);

        return view('opportunities.show', [
            'opportunity' => $opportunity,
            'bestNextAction' => $bestNextActionService->forOpportunity($opportunity),
            'timelineEvents' => $activityTimelineService->forOpportunity($opportunity),
        ]);
    }

    public function edit(Opportunity $opportunity): View
    {
        $this->authorize('update', $opportunity);

        return view('opportunities.edit', [
            'opportunity' => $opportunity,
            'contacts' => Contact::query()
                ->with('company')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
            'stages' => OpportunityStage::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreOpportunityRequest $request): RedirectResponse
    {
        Opportunity::query()->create($this->normalizeOpportunityPayload($request->validated()));

        return $this->successRedirect($request, 'Firsat kaydedildi.');
    }

    public function updateStage(
        UpdateOpportunityStageRequest $request,
        Opportunity $opportunity,
        AuditLogger $auditLogger,
        StageTaskTemplateService $stageTaskTemplateService,
    ): RedirectResponse {
        $this->applyStageTransition(
            opportunity: $opportunity,
            targetStageId: (int) $request->validated('opportunity_stage_id'),
            actorUserId: $request->user()?->id,
            auditLogger: $auditLogger,
            stageTaskTemplateService: $stageTaskTemplateService,
        );

        return $this->successRedirect($request, 'Firsat asamasi guncellendi.');
    }

    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update($this->normalizeOpportunityPayload($request->validated()));

        return $this->successRedirect($request, 'Firsat guncellendi.');
    }

    public function bulkStage(
        Request $request,
        AuditLogger $auditLogger,
        StageTaskTemplateService $stageTaskTemplateService,
    ): RedirectResponse {
        $this->authorize('viewAny', Opportunity::class);

        $validated = $request->validate([
            'opportunity_ids' => ['required', 'array', 'min:1'],
            'opportunity_ids.*' => ['required', 'integer', Rule::exists('opportunities', 'id')],
            'opportunity_stage_id' => ['required', 'integer', Rule::exists('opportunity_stages', 'id')],
        ]);

        $opportunityIds = collect($validated['opportunity_ids'])
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        DB::transaction(function () use ($opportunityIds, $validated, $request, $auditLogger, $stageTaskTemplateService): void {
            $opportunities = Opportunity::query()
                ->whereIn('id', $opportunityIds)
                ->lockForUpdate()
                ->get();

            foreach ($opportunities as $opportunity) {
                $this->authorize('update', $opportunity);

                $this->applyStageTransition(
                    opportunity: $opportunity,
                    targetStageId: (int) $validated['opportunity_stage_id'],
                    actorUserId: $request->user()?->id,
                    auditLogger: $auditLogger,
                    stageTaskTemplateService: $stageTaskTemplateService,
                );
            }
        });

        return redirect('/opportunities')->with('status', 'Secili firsatlarin asamasi guncellendi.');
    }

    public function kanban(Request $request): View
    {
        $this->authorize('viewAny', Opportunity::class);

        $stages = OpportunityStage::query()
            ->with(['opportunities' => fn ($query) => $query
                ->with(['contact.company', 'deal'])
                ->orderByDesc('expected_close_date')
                ->orderBy('title')])
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('opportunities.kanban', [
            'stages' => $stages,
        ]);
    }

    private function successRedirect(Request $request, string $message): RedirectResponse
    {
        $target = $request->user()?->can('viewAny', Opportunity::class)
            ? '/opportunities'
            : '/today';

        return redirect($target)->with('status', $message);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeOpportunityPayload(array $validated): array
    {
        if (! array_key_exists('probability', $validated) || $validated['probability'] === null) {
            $validated['probability'] = 50;
        }

        if (! array_key_exists('health_status', $validated) || $validated['health_status'] === null) {
            $validated['health_status'] = 'watch';
        }

        return $validated;
    }

    private function applyStageTransition(
        Opportunity $opportunity,
        int $targetStageId,
        ?int $actorUserId,
        AuditLogger $auditLogger,
        StageTaskTemplateService $stageTaskTemplateService,
    ): void {
        $beforeStageId = (int) $opportunity->opportunity_stage_id;
        if ($beforeStageId === $targetStageId) {
            return;
        }

        $opportunity->update([
            'opportunity_stage_id' => $targetStageId,
        ]);

        $this->ensureStageFollowUpTask($opportunity, $targetStageId, $stageTaskTemplateService);

        $auditLogger->log(
            userId: $actorUserId,
            entityType: Opportunity::class,
            entityId: $opportunity->id,
            action: 'opportunity_stage_changed',
            payload: [
                'from_stage' => $this->stageName($beforeStageId),
                'to_stage' => $this->stageName($targetStageId),
            ],
        );
    }

    private function ensureStageFollowUpTask(
        Opportunity $opportunity,
        int $targetStageId,
        StageTaskTemplateService $stageTaskTemplateService,
    ): void {
        $targetStage = OpportunityStage::query()->find($targetStageId);
        if (! $targetStage instanceof OpportunityStage) {
            return;
        }

        $template = $stageTaskTemplateService->templateForStage($targetStage);

        $hasOpenStageTask = CrmTask::query()
            ->where('opportunity_id', $opportunity->id)
            ->where('task_type', 'stage_follow_up')
            ->where('title', $template['title'])
            ->whereNull('completed_at')
            ->exists();

        if ($hasOpenStageTask) {
            return;
        }

        CrmTask::query()->create([
            'opportunity_id' => $opportunity->id,
            'assigned_user_id' => $opportunity->owner_user_id,
            'title' => $template['title'],
            'priority' => $template['priority'],
            'task_type' => 'stage_follow_up',
            'due_at' => now()->addHours($template['due_in_hours']),
        ]);
    }

    private function stageName(int $stageId): string
    {
        return (string) (
            OpportunityStage::query()
                ->whereKey($stageId)
                ->value('name')
            ?? 'Belirsiz'
        );
    }
}
