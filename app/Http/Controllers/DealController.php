<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertOpportunityToDealRequest;
use App\Http\Requests\StoreDealRequest;
use App\Models\Deal;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DealController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Deal::class);

        return view('deals.index', [
            'deals' => Deal::query()
                ->with(['opportunity.contact.company'])
                ->orderByDesc('closed_at')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Deal::class);

        return view('deals.create', [
            'opportunities' => Opportunity::query()
                ->with(['contact.company'])
                ->whereDoesntHave('deal')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function store(StoreDealRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $opportunity = Opportunity::query()->findOrFail($validated['opportunity_id']);

        $this->createDealForOpportunity(
            $opportunity,
            $validated['amount'] ?? null,
            $validated['closed_at'] ?? null,
        );

        return $this->successRedirect($request, 'Anlasma kaydedildi.');
    }

    public function convert(ConvertOpportunityToDealRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $this->createDealForOpportunity($opportunity, $opportunity->value, now());

        return $this->successRedirect($request, 'Firsat anlasmaya donusturuldu.');
    }

    private function successRedirect(Request $request, string $message): RedirectResponse
    {
        $target = $request->user()?->can('viewAny', Deal::class)
            ? '/deals'
            : '/today';

        return redirect($target)->with('status', $message);
    }

    private function createDealForOpportunity(Opportunity $opportunity, mixed $amount, mixed $closedAt): Deal
    {
        return DB::transaction(function () use ($opportunity, $amount, $closedAt): Deal {
            $lockedOpportunity = Opportunity::query()
                ->whereKey($opportunity->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOpportunity->deal()->exists()) {
                throw ValidationException::withMessages([
                    'opportunity_id' => 'Bu firsat zaten bir anlasmaya donusturuldu.',
                ]);
            }

            return Deal::query()->create([
                'opportunity_id' => $lockedOpportunity->id,
                'amount' => $amount,
                'closed_at' => $closedAt,
            ]);
        });
    }
}
