<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCrmTaskRequest;
use App\Models\CrmTask;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrmTaskController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', CrmTask::class);

        return view('tasks.index', [
            'now' => now(),
            'tasks' => CrmTask::query()
                ->with(['opportunity.contact.company'])
                ->orderByRaw('case when completed_at is null and due_at is not null and due_at < ? then 0 else 1 end', [now()])
                ->orderBy('due_at')
                ->orderBy('title')
                ->get(),
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

    public function store(StoreCrmTaskRequest $request): RedirectResponse
    {
        CrmTask::query()->create($request->validated());

        return $this->successRedirect($request);
    }

    private function successRedirect(Request $request): RedirectResponse
    {
        $target = $request->user()?->can('viewAny', CrmTask::class)
            ? '/tasks'
            : '/today';

        return redirect($target)->with('status', 'Gorev kaydedildi.');
    }
}
