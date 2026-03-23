<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Contact::class);

        return view('contacts.index', [
            'contacts' => Contact::query()
                ->with('company')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('viewAny', Contact::class);

        return view('contacts.create', [
            'companies' => Company::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        Contact::query()->create($request->validated());

        return redirect('/contacts');
    }
}
