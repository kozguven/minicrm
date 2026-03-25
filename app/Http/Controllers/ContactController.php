<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use App\Services\Actions\BestNextActionService;
use App\Services\Timeline\ActivityTimelineService;
use App\Services\Validation\DuplicateRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contact::class);

        $search = trim((string) $request->query('q', ''));

        return view('contacts.index', [
            'contacts' => Contact::query()
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
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
            'filters' => [
                'q' => $search,
            ],
        ]);
    }

    public function show(
        Contact $contact,
        BestNextActionService $bestNextActionService,
        ActivityTimelineService $activityTimelineService,
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
}
