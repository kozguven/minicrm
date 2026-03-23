<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $search = trim((string) $request->query('q', ''));

        return view('companies.index', [
            'companies' => Company::query()
                ->when($search !== '', function ($query) use ($search): void {
                    $like = "%{$search}%";

                    $query->where(function ($nestedQuery) use ($like): void {
                        $nestedQuery
                            ->where('name', 'like', $like)
                            ->orWhere('website', 'like', $like);
                    });
                })
                ->withCount('contacts')
                ->orderBy('name')
                ->get(),
            'filters' => [
                'q' => $search,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('companies.create');
    }

    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        return view('companies.show', [
            'company' => $company->load([
                'contacts' => fn ($query) => $query
                    ->withCount('opportunities')
                    ->orderBy('first_name')
                    ->orderBy('last_name'),
            ]),
        ]);
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::query()->create($request->validated());

        return redirect('/companies');
    }
}
