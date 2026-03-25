<?php

namespace App\Http\Controllers;

use App\Services\Permissions\PermissionResolver;
use App\Services\Reports\PipelineMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function pipeline(
        Request $request,
        PermissionResolver $permissionResolver,
        PipelineMetricsService $pipelineMetricsService,
    ): View|JsonResponse {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        $data = $pipelineMetricsService->pipeline();

        if ($request->expectsJson()) {
            return response()->json([
                'stages' => $data['stages']->map(fn ($stage): array => [
                    'id' => $stage->id,
                    'name' => $stage->name,
                    'position' => $stage->position,
                    'opportunities_count' => (int) ($stage->opportunities_count ?? 0),
                    'opportunities_value_sum' => (float) ($stage->opportunities_value_sum ?? 0),
                ])->values(),
            ]);
        }

        return view('reports.pipeline', $data);
    }

    public function forecast(
        Request $request,
        PermissionResolver $permissionResolver,
        PipelineMetricsService $pipelineMetricsService,
    ): View|JsonResponse {
        abort_unless(
            $permissionResolver->can($request->user(), 'companies.view'),
            403,
        );

        $data = $pipelineMetricsService->forecast();

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('reports.forecast', $data);
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
