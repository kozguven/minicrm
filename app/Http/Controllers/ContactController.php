<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use App\Services\Actions\BestNextActionService;
use App\Services\Leads\LeadScoringService;
use App\Services\Timeline\ActivityTimelineService;
use App\Services\Validation\DuplicateRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request, LeadScoringService $leadScoringService): View
    {
        $this->authorize('viewAny', Contact::class);

        $search = trim((string) $request->query('q', ''));
        $leadStatus = strtolower((string) $request->query('lead_status', 'all'));
        $priority = strtolower((string) $request->query('priority', 'all'));
        $sort = strtolower((string) $request->query('sort', 'name_asc'));
        $lastContactFrom = trim((string) $request->query('last_contact_from', ''));
        $lastContactTo = trim((string) $request->query('last_contact_to', ''));
        $allowedLeadStatuses = ['all', 'new', 'contacted', 'qualified', 'lost'];
        $allowedPriorities = ['all', 'low', 'medium', 'high'];
        $allowedSorts = ['name_asc', 'name_desc', 'last_contact_desc', 'last_contact_asc'];
        $normalizedLeadStatus = in_array($leadStatus, $allowedLeadStatuses, true) ? $leadStatus : 'all';
        $normalizedPriority = in_array($priority, $allowedPriorities, true) ? $priority : 'all';
        $normalizedSort = in_array($sort, $allowedSorts, true) ? $sort : 'name_asc';

        $contacts = Contact::query()
            ->with('company')
            ->when($search !== '', function ($query) use ($search): void {
                $like = "%{$search}%";

                $query->where(function ($nestedQuery) use ($like): void {
                    $nestedQuery
                        ->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhereHas('company', fn ($companyQuery) => $companyQuery
                            ->where('name', 'like', $like));
                });
            })
            ->when($normalizedLeadStatus !== 'all', fn ($query) => $query
                ->where('lead_status', $normalizedLeadStatus))
            ->when($normalizedPriority !== 'all', fn ($query) => $query
                ->where('priority', $normalizedPriority))
            ->when($lastContactFrom !== '', fn ($query) => $query
                ->whereDate('last_contacted_at', '>=', $lastContactFrom))
            ->when($lastContactTo !== '', fn ($query) => $query
                ->whereDate('last_contacted_at', '<=', $lastContactTo));

        match ($normalizedSort) {
            'name_desc' => $contacts->orderByDesc('first_name')->orderByDesc('last_name'),
            'last_contact_desc' => $contacts->orderByDesc('last_contacted_at')->orderBy('first_name')->orderBy('last_name'),
            'last_contact_asc' => $contacts->orderBy('last_contacted_at')->orderBy('first_name')->orderBy('last_name'),
            default => $contacts->orderBy('first_name')->orderBy('last_name'),
        };

        $contacts = $contacts->paginate(20)->withQueryString();
        $leadInsights = $contacts->getCollection()
            ->mapWithKeys(fn (Contact $contact): array => [
                $contact->id => $this->buildLeadInsight($contact, $leadScoringService),
            ])
            ->all();

        return view('contacts.index', [
            'contacts' => $contacts,
            'leadInsights' => $leadInsights,
            'filters' => [
                'q' => $search,
                'lead_status' => $normalizedLeadStatus,
                'priority' => $normalizedPriority,
                'sort' => $normalizedSort,
                'last_contact_from' => $lastContactFrom,
                'last_contact_to' => $lastContactTo,
            ],
        ]);
    }

    public function show(
        Contact $contact,
        BestNextActionService $bestNextActionService,
        ActivityTimelineService $activityTimelineService,
        LeadScoringService $leadScoringService,
    ): View {
        $this->authorize('view', $contact);

        $contact = $contact->load([
            'company',
            'opportunities' => fn ($query) => $query
                ->with(['opportunityStage', 'deal'])
                ->orderByDesc('expected_close_date')
                ->orderBy('title'),
            'contactInteractions' => fn ($query) => $query
                ->with('user')
                ->orderByDesc('happened_at')
                ->orderByDesc('id'),
        ]);

        return view('contacts.show', [
            'contact' => $contact,
            'bestNextAction' => $bestNextActionService->forContact($contact),
            'timelineEvents' => $activityTimelineService->forContact($contact),
            'leadInsight' => $this->buildLeadInsight($contact, $leadScoringService),
        ]);
    }

    public function edit(Contact $contact): View
    {
        $this->authorize('update', $contact);

        return view('contacts.edit', [
            'contact' => $contact,
            'companies' => Company::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(
        StoreContactRequest $request,
        DuplicateRecordService $duplicateRecordService,
    ): RedirectResponse {
        $validated = $request->validated();
        $warnings = $duplicateRecordService->contactWarnings(
            email: $validated['email'] ?? null,
            phone: $validated['phone'] ?? null,
        );

        if ($warnings !== []) {
            return back()
                ->withInput()
                ->withErrors(['duplicate' => implode(' ', $warnings)]);
        }

        Contact::query()->create($validated);

        return redirect('/contacts')->with('status', 'Kisi kaydedildi.');
    }

    public function update(
        UpdateContactRequest $request,
        Contact $contact,
        DuplicateRecordService $duplicateRecordService,
    ): RedirectResponse {
        $validated = $request->validated();
        $warnings = $duplicateRecordService->contactWarnings(
            email: $validated['email'] ?? null,
            phone: $validated['phone'] ?? null,
            ignoreContactId: $contact->id,
        );

        if ($warnings !== []) {
            return back()
                ->withInput()
                ->withErrors(['duplicate' => implode(' ', $warnings)]);
        }

        $contact->update($validated);

        return redirect('/contacts')->with('status', 'Kisi guncellendi.');
    }

    public function create(): View
    {
        $this->authorize('create', Contact::class);

        return view('contacts.create', [
            'companies' => Company::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * @return array{score: int, label: string, badge: string}
     */
    private function buildLeadInsight(Contact $contact, LeadScoringService $leadScoringService): array
    {
        $score = $leadScoringService->score($contact);

        if ($score >= 75) {
            return [
                'score' => $score,
                'label' => 'Sicak Lead',
                'badge' => 'badge--success',
            ];
        }

        if ($score >= 45) {
            return [
                'score' => $score,
                'label' => 'Ilik Lead',
                'badge' => 'badge--info',
            ];
        }

        return [
            'score' => $score,
            'label' => 'Soguk Lead',
            'badge' => 'badge--danger',
        ];
    }
}
