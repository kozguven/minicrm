<?php

namespace App\Http\Controllers;

use App\Services\Today\TodayPriorityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodayController extends Controller
{
    public function __invoke(Request $request, TodayPriorityService $todayPriorityService): View
    {
        return view('today.index', [
            'sections' => $todayPriorityService->buildFor($request->user()),
        ]);
    }
}
