<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Company;
use App\Models\Contact;
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

    public function create(): View
    {
        $this->authorize('create', Contact::class);

        return view('contacts.create', [
            'companies' => Company::query()->orderBy('name')->get(),
        ]);
    }

    public function show(Contact $contact): View
    {
        $this->authorize('view', $contact);

        return view('contacts.show', [
            'contact' => $contact->load([
                'company',
                'opportunities' => fn ($query) => $query
                    ->with(['opportunityStage', 'deal'])
                    ->orderByDesc('expected_close_date')
                    ->orderBy('title'),
            ]),
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        Contact::query()->create($request->validated());

        return redirect('/contacts');
    }
}
