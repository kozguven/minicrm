<?php

namespace App\Http\Controllers;

use App\Services\Permissions\PermissionResolver;
use App\Services\Search\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(
        Request $request,
        PermissionResolver $permissionResolver,
        GlobalSearchService $globalSearchService,
    ): View {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        $query = trim((string) $request->query('q', ''));
        $results = $globalSearchService->search($query);

        return view('search.global', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
