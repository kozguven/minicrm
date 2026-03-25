<?php

namespace App\Http\Controllers;

use App\Services\Permissions\PermissionResolver;
use App\Services\Search\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(
        Request $request,
        PermissionResolver $permissionResolver,
        GlobalSearchService $globalSearchService,
    ): View|JsonResponse {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        $query = trim((string) $request->query('q', ''));
        $results = $globalSearchService->search($query);

        if ($request->expectsJson()) {
            return response()->json([
                'query' => $query,
                'counts' => [
                    'companies' => $results['companies']->count(),
                    'contacts' => $results['contacts']->count(),
                    'opportunities' => $results['opportunities']->count(),
                    'tasks' => $results['tasks']->count(),
                ],
                'results' => [
                    'companies' => $results['companies']->map(fn ($company): array => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'website' => $company->website,
                        'url' => url("/companies/{$company->id}"),
                    ])->values(),
                    'contacts' => $results['contacts']->map(fn ($contact): array => [
                        'id' => $contact->id,
                        'full_name' => trim("{$contact->first_name} {$contact->last_name}"),
                        'email' => $contact->email,
                        'phone' => $contact->phone,
                        'company' => $contact->company?->name,
                        'url' => url("/contacts/{$contact->id}"),
                    ])->values(),
                    'opportunities' => $results['opportunities']->map(fn ($opportunity): array => [
                        'id' => $opportunity->id,
                        'title' => $opportunity->title,
                        'stage' => $opportunity->opportunityStage?->name,
                        'contact' => trim("{$opportunity->contact?->first_name} {$opportunity->contact?->last_name}"),
                        'company' => $opportunity->contact?->company?->name,
                        'next_step' => $opportunity->next_step,
                        'expected_close_date' => optional($opportunity->expected_close_date)?->toDateString(),
                        'url' => url("/opportunities/{$opportunity->id}"),
                    ])->values(),
                    'tasks' => $results['tasks']->map(fn ($task): array => [
                        'id' => $task->id,
                        'title' => $task->title,
                        'due_at' => optional($task->due_at)?->toDateTimeString(),
                        'completed_at' => optional($task->completed_at)?->toDateTimeString(),
                        'opportunity' => $task->opportunity?->title,
                        'company' => $task->opportunity?->contact?->company?->name,
                        'url' => url("/tasks/{$task->id}"),
                    ])->values(),
                ],
            ]);
        }

        return view('search.global', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
