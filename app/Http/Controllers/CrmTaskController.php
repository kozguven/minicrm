<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCrmTaskRequest;
use App\Http\Requests\UpdateCrmTaskRequest;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CrmTaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CrmTask::class);

        $search = trim((string) $request->query('q', ''));
        $status = strtolower((string) $request->query('status', 'all'));
        $priority = strtolower((string) $request->query('priority', 'all'));
        $sort = strtolower((string) $request->query('sort', 'due_asc'));
        $allowedStatuses = ['all', 'open', 'overdue', 'completed'];
        $normalizedStatus = in_array($status, $allowedStatuses, true) ? $status : 'all';
        $allowedPriorities = ['all', 'low', 'medium', 'high'];
        $normalizedPriority = in_array($priority, $allowedPriorities, true) ? $priority : 'all';
        $allowedSorts = ['due_asc', 'due_desc', 'priority_desc', 'title_asc'];
        $normalizedSort = in_array($sort, $allowedSorts, true) ? $sort : 'due_asc';
        $now = now();

        $tasks = CrmTask::query()
            ->with(['opportunity.contact.company'])
            ->when($search !== '', function ($query) use ($search): void {
                $like = "%{$search}%";

                $query->where(function ($nestedQuery) use ($like): void {
                    $nestedQuery
                        ->where('title', 'like', $like)
                        ->orWhereHas('opportunity', function ($opportunityQuery) use ($like): void {
                            $opportunityQuery
                                ->where('title', 'like', $like)
                                ->orWhereHas('contact', function ($contactQuery) use ($like): void {
                                    $contactQuery
                                        ->where('first_name', 'like', $like)
                                        ->orWhere('last_name', 'like', $like)
                                        ->orWhereHas('company', fn ($companyQuery) => $companyQuery
                                            ->where('name', 'like', $like));
                                });
                        });
                });
            });

        match ($normalizedStatus) {
            'open' => $tasks->whereNull('completed_at'),
            'overdue' => $tasks
                ->whereNull('completed_at')
                ->whereNotNull('due_at')
                ->where('due_at', '<', $now),
            'completed' => $tasks->whereNotNull('completed_at'),
            default => null,
        };

        if ($normalizedPriority !== 'all') {
            $tasks->where('priority', $normalizedPriority);
        }

        match ($normalizedSort) {
            'due_desc' => $tasks->orderByDesc('due_at')->orderBy('title'),
            'priority_desc' => $tasks
                ->orderByRaw("case priority when 'high' then 0 when 'medium' then 1 else 2 end")
                ->orderBy('due_at')
                ->orderBy('title'),
            'title_asc' => $tasks->orderBy('title'),
            default => $tasks
                ->orderByRaw('case when completed_at is null and due_at is not null and due_at < ? then 0 else 1 end', [now()])
                ->orderBy('due_at')
                ->orderBy('title'),
        };

        return view('tasks.index', [
            'tasks' => $tasks->paginate(20)->withQueryString(),
            'filters' => [
                'q' => $search,
                'status' => $normalizedStatus,
                'priority' => $normalizedPriority,
                'sort' => $normalizedSort,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CrmTask::class);

        return view('tasks.create', [
            'opportunities' => Opportunity::query()
                ->with(['contact.company'])
                ->orderBy('title')
                ->get(),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function show(CrmTask $crmTask): View
    {
        $this->authorize('view', $crmTask);

        return view('tasks.show', [
            'task' => $crmTask->load([
                'opportunity.contact.company',
                'opportunity.opportunityStage',
            ]),
        ]);
    }

    public function edit(CrmTask $crmTask): View
    {
        $this->authorize('update', $crmTask);

        return view('tasks.edit', [
            'task' => $crmTask,
            'opportunities' => Opportunity::query()
                ->with(['contact.company'])
                ->orderBy('title')
                ->get(),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCrmTaskRequest $request): RedirectResponse
    {
        CrmTask::query()->create($this->normalizeTaskPayload($request->validated()));

        return $this->successRedirect($request);
    }

    public function toggleComplete(Request $request, CrmTask $crmTask): RedirectResponse
    {
        $this->authorize('update', $crmTask);

        $wasCompleted = $crmTask->completed_at !== null;
        $crmTask->update([
            'completed_at' => $wasCompleted ? null : now(),
        ]);

        $target = $request->headers->get('referer', '/tasks');

        return redirect($target)->with(
            'status',
            $wasCompleted ? 'Gorev tekrar acildi.' : 'Gorev tamamlandi.',
        );
    }

    public function update(UpdateCrmTaskRequest $request, CrmTask $crmTask): RedirectResponse
    {
        $crmTask->update($this->normalizeTaskPayload($request->validated()));

        return redirect('/tasks')->with('status', 'Gorev guncellendi.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', CrmTask::class);

        $validated = $request->validate([
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer', Rule::exists('crm_tasks', 'id')],
            'action' => ['required', 'string', Rule::in(['complete', 'reopen'])],
        ]);

        $taskIds = collect($validated['task_ids'])
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        DB::transaction(function () use ($taskIds, $validated): void {
            $tasks = CrmTask::query()
                ->whereIn('id', $taskIds)
                ->lockForUpdate()
                ->get();

            foreach ($tasks as $task) {
                $this->authorize('update', $task);
            }

            CrmTask::query()
                ->whereIn('id', $taskIds)
                ->update([
                    'completed_at' => $validated['action'] === 'complete' ? now() : null,
                ]);
        });

        return redirect('/tasks')->with(
            'status',
            $validated['action'] === 'complete'
                ? 'Secili gorevler tamamlandi.'
                : 'Secili gorevler yeniden acildi.',
        );
    }

    private function successRedirect(Request $request): RedirectResponse
    {
        $target = $request->user()?->can('viewAny', CrmTask::class)
            ? '/tasks'
            : '/today';

        return redirect($target)->with('status', 'Gorev kaydedildi.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeTaskPayload(array $validated): array
    {
        if (! array_key_exists('priority', $validated) || $validated['priority'] === null) {
            $validated['priority'] = 'medium';
        }

        if (! array_key_exists('task_type', $validated) || $validated['task_type'] === null) {
            $validated['task_type'] = 'manual';
        }

        return $validated;
    }
}
