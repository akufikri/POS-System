<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardService
{
    // ─── Owner ─────────────────────────────────────────────────────────────────

    public function getOwnerSummary(int $tenantId): array
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $todayOrders = Order::whereDate('created_at', $today)->get();
        $monthOrders = Order::where('created_at', '>=', $thisMonth)->get();

        return [
            'today_revenue'      => $todayOrders->sum('total_amount'),
            'today_profit'       => $todayOrders->sum(fn($o) => $o->total_amount - $o->total_cost),
            'today_transactions' => $todayOrders->count(),
            'month_revenue'      => $monthOrders->sum('total_amount'),
            'month_profit'       => $monthOrders->sum(fn($o) => $o->total_amount - $o->total_cost),
            'month_transactions' => $monthOrders->count(),
            'kasir_performance'  => $this->getKasirPerformance($tenantId, $today),
        ];
    }

    public function getOwnerChartData(int $tenantId): array
    {
        $days = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i));

        $labels  = [];
        $revenue = [];
        $profit  = [];

        foreach ($days as $day) {
            $orders    = Order::whereDate('created_at', $day)->get();
            $labels[]  = $day->format('D, d M');
            $revenue[] = $orders->sum('total_amount');
            $profit[]  = $orders->sum(fn($o) => $o->total_amount - $o->total_cost);
        }

        return compact('labels', 'revenue', 'profit');
    }

    public function getOwnerSummaryForApi(int $tenantId): array
    {
        $s = $this->getOwnerSummary($tenantId);

        return [
            'today_revenue'          => $s['today_revenue'],
            'today_profit'           => $s['today_profit'],
            'today_transactions'     => $s['today_transactions'],
            'month_revenue'          => $s['month_revenue'],
            'month_profit'           => $s['month_profit'],
            'month_transactions'     => $s['month_transactions'],
            'revenue_formatted'      => $this->rp($s['today_revenue']),
            'profit_formatted'       => $this->rp($s['today_profit']),
            'month_revenue_formatted'=> $this->rp($s['month_revenue']),
            'month_profit_formatted' => $this->rp($s['month_profit']),
            'kasir_performance'      => $s['kasir_performance'],
        ];
    }

    // ─── Cashier ───────────────────────────────────────────────────────────────

    public function getCashierSummary(int $userId): array
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $todayOrders = Order::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->get();

        $monthOrders = Order::where('user_id', $userId)
            ->where('created_at', '>=', $thisMonth)
            ->get();

        return [
            'today_revenue'      => $todayOrders->sum('total_amount'),
            'today_transactions' => $todayOrders->count(),
            'month_revenue'      => $monthOrders->sum('total_amount'),
            'month_transactions' => $monthOrders->count(),
        ];
    }

    public function getCashierChartData(int $userId): array
    {
        $days = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i));

        $labels       = [];
        $transactions = [];
        $revenue      = [];

        foreach ($days as $day) {
            $orders = Order::where('user_id', $userId)
                ->whereDate('created_at', $day)
                ->get();

            $labels[]       = $day->format('D, d M');
            $transactions[] = $orders->count();
            $revenue[]      = $orders->sum('total_amount');
        }

        return compact('labels', 'transactions', 'revenue');
    }

    public function getCashierSummaryForApi(int $userId): array
    {
        $s = $this->getCashierSummary($userId);

        return [
            'today_revenue'           => $s['today_revenue'],
            'today_transactions'      => $s['today_transactions'],
            'month_revenue'           => $s['month_revenue'],
            'month_transactions'      => $s['month_transactions'],
            'revenue_formatted'       => $this->rp($s['today_revenue']),
            'month_revenue_formatted' => $this->rp($s['month_revenue']),
        ];
    }

    // ─── Shared ────────────────────────────────────────────────────────────────

    private function getKasirPerformance(int $tenantId, Carbon $date): array
    {
        return User::where('tenant_id', $tenantId)
            ->where('role', 'cashier')
            ->withCount(['orders as transaction_count' => fn($q) =>
                $q->whereDate('created_at', $date)
            ])
            ->withSum(['orders as total_revenue' => fn($q) =>
                $q->whereDate('created_at', $date)
            ], 'total_amount')
            ->orderByDesc('transaction_count')
            ->get()
            ->map(fn($user) => [
                'name'              => $user->name,
                'transaction_count' => $user->transaction_count ?? 0,
                'total_revenue'     => $user->total_revenue ?? 0,
                'revenue_formatted' => $this->rp($user->total_revenue ?? 0),
            ])
            ->toArray();
    }

    private function rp(int|float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
