<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertOpportunityToDealRequest;
use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;
use App\Models\Opportunity;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DealController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Deal::class);

        $search = trim((string) $request->query('q', ''));
        $closedFrom = trim((string) $request->query('closed_from', ''));
        $closedTo = trim((string) $request->query('closed_to', ''));
        $sort = (string) $request->query('sort', 'closed_desc');
        $allowedSorts = ['closed_desc', 'closed_asc', 'amount_desc', 'amount_asc'];
        $normalizedSort = in_array($sort, $allowedSorts, true) ? $sort : 'closed_desc';

        $deals = Deal::query()
            ->with(['opportunity.contact.company'])
            ->when($search !== '', function ($query) use ($search): void {
                $like = "%{$search}%";

                $query->whereHas('opportunity', function ($opportunityQuery) use ($like): void {
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
            })
            ->when($closedFrom !== '', fn ($query) => $query
                ->whereDate('closed_at', '>=', $closedFrom))
            ->when($closedTo !== '', fn ($query) => $query
                ->whereDate('closed_at', '<=', $closedTo));

        match ($normalizedSort) {
            'closed_asc' => $deals->orderBy('closed_at')->orderBy('id'),
            'amount_desc' => $deals->orderByDesc('amount')->orderByDesc('closed_at'),
            'amount_asc' => $deals->orderBy('amount')->orderByDesc('closed_at'),
            default => $deals->orderByDesc('closed_at')->orderByDesc('id'),
        };

        return view('deals.index', [
            'deals' => $deals->paginate(20)->withQueryString(),
            'filters' => [
                'q' => $search,
                'closed_from' => $closedFrom,
                'closed_to' => $closedTo,
                'sort' => $normalizedSort,
            ],
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

    public function show(Deal $deal): View
    {
        $this->authorize('view', $deal);

        return view('deals.show', [
            'deal' => $deal->load([
                'opportunity.contact.company',
                'opportunity.opportunityStage',
            ]),
        ]);
    }

    public function edit(Deal $deal): View
    {
        $this->authorize('update', $deal);

        return view('deals.edit', [
            'deal' => $deal->load([
                'opportunity.contact.company',
            ]),
        ]);
    }

    public function store(StoreDealRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validated();
        $opportunity = Opportunity::query()->findOrFail($validated['opportunity_id']);

        $deal = $this->createDealForOpportunity(
            $opportunity,
            $validated['amount'] ?? null,
            $validated['closed_at'] ?? null,
        );

        $auditLogger->log(
            userId: $request->user()?->id,
            entityType: Deal::class,
            entityId: $deal->id,
            action: 'deal_created',
            payload: [
                'source' => 'create',
                'opportunity_id' => $opportunity->id,
                'amount' => $this->normalizeAmountForAudit($validated['amount'] ?? null),
            ],
        );

        return $this->successRedirect($request, 'Anlasma kaydedildi.');
    }

    public function convert(
        ConvertOpportunityToDealRequest $request,
        Opportunity $opportunity,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $deal = $this->createDealForOpportunity($opportunity, $opportunity->value, now());

        $auditLogger->log(
            userId: $request->user()?->id,
            entityType: Deal::class,
            entityId: $deal->id,
            action: 'deal_created',
            payload: [
                'source' => 'convert',
                'opportunity_id' => $opportunity->id,
                'amount' => $this->normalizeAmountForAudit($opportunity->value),
            ],
        );

        return $this->successRedirect($request, 'Firsat anlasmaya donusturuldu.');
    }

    public function update(UpdateDealRequest $request, Deal $deal): RedirectResponse
    {
        $deal->update($request->validated());

        return $this->successRedirect($request, 'Anlasma guncellendi.');
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

    private function normalizeAmountForAudit(mixed $amount): mixed
    {
        if ($amount === null || ! is_numeric($amount)) {
            return null;
        }

        $numeric = (float) $amount;

        return fmod($numeric, 1.0) === 0.0
            ? (int) $numeric
            : $numeric;
    }
}
