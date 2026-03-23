<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityStageRequest;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Opportunity::class);

        return view('opportunities.index', [
            'opportunities' => Opportunity::query()
                ->with(['contact.company', 'opportunityStage'])
                ->orderByDesc('expected_close_date')
                ->orderBy('title')
                ->get(),
            'stages' => OpportunityStage::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(),
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

    public function store(StoreOpportunityRequest $request): RedirectResponse
    {
        Opportunity::query()->create($request->validated());

        return redirect('/opportunities');
    }

    public function updateStage(UpdateOpportunityStageRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update($request->validated());

        return redirect('/opportunities');
    }
}
