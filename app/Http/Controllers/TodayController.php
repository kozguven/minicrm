<?php

namespace App\Http\Controllers;

use App\Services\Automation\SlaFollowUpService;
use App\Services\Permissions\PermissionResolver;
use App\Services\Today\TodayPriorityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodayController extends Controller
{
    public function __invoke(
        Request $request,
        TodayPriorityService $todayPriorityService,
        PermissionResolver $permissionResolver,
        SlaFollowUpService $slaFollowUpService,
    ): View {
        $canViewCrm = $permissionResolver->can($request->user(), 'companies.view');

        if ($canViewCrm) {
            $slaFollowUpService->run();
        }

        return view('today.index', [
            'canViewCrm' => $canViewCrm,
            'permissionMessage' => $canViewCrm ? null : 'CRM verilerini görmek için yetki gerekli.',
            'sections' => $canViewCrm ? $todayPriorityService->build() : [],
        ]);
    }
}
