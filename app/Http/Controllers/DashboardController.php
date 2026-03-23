<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Opportunity;
use App\Services\Permissions\PermissionResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PermissionResolver $permissionResolver): View
    {
        $canViewCrm = $permissionResolver->can($request->user(), 'companies.view');
        $now = Carbon::now();

        $metrics = [
            'open_opportunities' => Opportunity::query()
                ->whereDoesntHave('deal')
                ->count(),
            'weekly_closed_deals' => Deal::query()
                ->whereNotNull('closed_at')
                ->whereBetween('closed_at', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek(),
                ])
                ->count(),
        ];

        return view('dashboard.index', [
            'canViewCrm' => $canViewCrm,
            'permissionMessage' => $canViewCrm ? null : 'CRM verilerini görmek için yetki gerekli.',
            'metrics' => $metrics,
        ]);
    }
}
