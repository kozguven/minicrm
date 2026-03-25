<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Requests\UpdateOpportunityStageRequest;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Opportunity::class);

        $search = trim((string) $request->query('q', ''));

        return view('opportunities.index', [
            'opportunities' => Opportunity::query()
                ->with(['contact.company', 'opportunityStage', 'deal'])
                ->when($search !== '', function ($query) use ($search): void {
                    $like = "%{$search}%";

                    $query->where(function ($nestedQuery) use ($like): void {
                        $nestedQuery
                            ->where('title', 'like', $like)
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
                ->orderByDesc('expected_close_date')
                ->orderBy('title')
                ->get(),
            'stages' => OpportunityStage::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(),
            'filters' => [
                'q' => $search,
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
        ]);
    }

    public function show(Opportunity $opportunity): View
    {
        $this->authorize('view', $opportunity);

        return view('opportunities.show', [
            'opportunity' => $opportunity->load([
                'contact.company',
                'opportunityStage',
                'deal',
                'tasks' => fn ($query) => $query
                    ->orderByRaw('case when completed_at is null and due_at is not null and due_at < ? then 0 else 1 end', [now()])
                    ->orderBy('due_at')
                    ->orderBy('title'),
            ]),
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
        ]);
    }

    public function store(StoreOpportunityRequest $request): RedirectResponse
    {
        Opportunity::query()->create($request->validated());

        return $this->successRedirect($request, 'Firsat kaydedildi.');
    }

    public function updateStage(UpdateOpportunityStageRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update($request->validated());

        return $this->successRedirect($request, 'Firsat asamasi guncellendi.');
    }

    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update($request->validated());

        return $this->successRedirect($request, 'Firsat guncellendi.');
    }

    private function successRedirect(Request $request, string $message): RedirectResponse
    {
        $target = $request->user()?->can('viewAny', Opportunity::class)
            ? '/opportunities'
            : '/today';

        return redirect($target)->with('status', $message);
    }
}
