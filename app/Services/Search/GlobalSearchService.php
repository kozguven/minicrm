<?php

namespace App\Services\Search;

use App\Models\Company;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    /**
     * @return array{
     *   companies: Collection<int, Company>,
     *   contacts: Collection<int, Contact>,
     *   opportunities: Collection<int, Opportunity>,
     *   tasks: Collection<int, CrmTask>
     * }
     */
    public function search(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return [
                'companies' => collect(),
                'contacts' => collect(),
                'opportunities' => collect(),
                'tasks' => collect(),
            ];
        }

        $like = "%{$query}%";

        $companies = Company::query()
            ->where('name', 'like', $like)
            ->orWhere('website', 'like', $like)
            ->orderBy('name')
            ->limit(8)
            ->get();

        $contacts = Contact::query()
            ->with('company')
            ->where(function ($nestedQuery) use ($like): void {
                $nestedQuery
                    ->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhereHas('company', fn ($companyQuery) => $companyQuery
                        ->where('name', 'like', $like));
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(8)
            ->get();

        $opportunities = Opportunity::query()
            ->with(['contact.company', 'opportunityStage'])
            ->where(function ($nestedQuery) use ($like): void {
                $nestedQuery
                    ->where('title', 'like', $like)
                    ->orWhere('next_step', 'like', $like)
                    ->orWhereHas('contact', fn ($contactQuery) => $contactQuery
                        ->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhereHas('company', fn ($companyQuery) => $companyQuery
                            ->where('name', 'like', $like)));
            })
            ->orderByDesc('expected_close_date')
            ->limit(8)
            ->get();

        $tasks = CrmTask::query()
            ->with(['opportunity.contact.company'])
            ->where(function ($nestedQuery) use ($like): void {
                $nestedQuery
                    ->where('title', 'like', $like)
                    ->orWhereHas('opportunity', fn ($opportunityQuery) => $opportunityQuery
                        ->where('title', 'like', $like)
                        ->orWhereHas('contact', fn ($contactQuery) => $contactQuery
                            ->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhereHas('company', fn ($companyQuery) => $companyQuery
                                ->where('name', 'like', $like))));
            })
            ->orderByRaw('case when completed_at is null then 0 else 1 end')
            ->orderBy('due_at')
            ->limit(8)
            ->get();

        return [
            'companies' => $companies,
            'contacts' => $contacts,
            'opportunities' => $opportunities,
            'tasks' => $tasks,
        ];
    }
}
