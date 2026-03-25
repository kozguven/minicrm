<?php

namespace App\Http\Controllers;

use App\Services\Permissions\PermissionResolver;
use App\Services\Reports\PipelineMetricsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function pipeline(
        Request $request,
        PermissionResolver $permissionResolver,
        PipelineMetricsService $pipelineMetricsService,
    ): View {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        return view('reports.pipeline', $pipelineMetricsService->pipeline());
    }

    public function forecast(
        Request $request,
        PermissionResolver $permissionResolver,
        PipelineMetricsService $pipelineMetricsService,
    ): View {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        return view('reports.forecast', $pipelineMetricsService->forecast());
    }

    public function funnel(
        Request $request,
        PermissionResolver $permissionResolver,
        PipelineMetricsService $pipelineMetricsService,
    ): View {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        return view('reports.funnel', $pipelineMetricsService->funnel());
    }

    public function salesCycle(
        Request $request,
        PermissionResolver $permissionResolver,
        PipelineMetricsService $pipelineMetricsService,
    ): View {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        return view('reports.sales-cycle', $pipelineMetricsService->salesCycle());
    }

    public function performance(
        Request $request,
        PermissionResolver $permissionResolver,
        PipelineMetricsService $pipelineMetricsService,
    ): View {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        return view('reports.performance', $pipelineMetricsService->performance());
    }

    public function dataQuality(
        Request $request,
        PermissionResolver $permissionResolver,
        PipelineMetricsService $pipelineMetricsService,
    ): View {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        return view('reports.data-quality', $pipelineMetricsService->dataQuality());
    }
}
