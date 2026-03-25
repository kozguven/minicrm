<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCrmTaskRequest;
use App\Http\Requests\UpdateCrmTaskRequest;
use App\Models\CrmTask;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrmTaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CrmTask::class);

        $search = trim((string) $request->query('q', ''));
        $status = strtolower((string) $request->query('status', 'all'));
        $allowedStatuses = ['all', 'open', 'overdue', 'completed'];
        $normalizedStatus = in_array($status, $allowedStatuses, true) ? $status : 'all';
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

        return view('tasks.index', [
            'tasks' => $tasks
                ->orderByRaw('case when completed_at is null and due_at is not null and due_at < ? then 0 else 1 end', [now()])
                ->orderBy('due_at')
                ->orderBy('title')
                ->get(),
            'filters' => [
                'q' => $search,
                'status' => $normalizedStatus,
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
        ]);
    }

    public function store(StoreCrmTaskRequest $request): RedirectResponse
    {
        CrmTask::query()->create($request->validated());

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
        $crmTask->update($request->validated());

        return redirect('/tasks')->with('status', 'Gorev guncellendi.');
    }

    private function successRedirect(Request $request): RedirectResponse
    {
        $target = $request->user()?->can('viewAny', CrmTask::class)
            ? '/tasks'
            : '/today';

        return redirect($target)->with('status', 'Gorev kaydedildi.');
    }
}
