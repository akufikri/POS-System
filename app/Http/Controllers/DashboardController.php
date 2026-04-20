<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): View
    {
        $user    = auth()->user();
        $isOwner = $user->isOwner();

        if ($isOwner) {
            $summary   = $this->dashboardService->getOwnerSummary($user->tenant_id);
            $chartData = $this->dashboardService->getOwnerChartData($user->tenant_id);
            return view('page.dashboard.index', compact('summary', 'chartData', 'isOwner'));
        } else {
            $summary   = $this->dashboardService->getCashierSummary($user->id);
            $chartData = $this->dashboardService->getCashierChartData($user->id);
            
            // For POS
            $categories = \App\Models\Category::orderBy('sort_order')->get();
            $products = \App\Models\Product::where('is_active', true)->orderBy('name')->get();
            
            return view('page.dashboard.index', compact('summary', 'chartData', 'isOwner', 'categories', 'products'));
        }
    }

    public function summary(): JsonResponse
    {
        $user = auth()->user();

        $data = $user->isOwner()
            ? $this->dashboardService->getOwnerSummaryForApi($user->tenant_id)
            : $this->dashboardService->getCashierSummaryForApi($user->id);

        return response()->json($data);
    }
}
