<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class ShiftService
{
    public function open(int $userId, int $openingCash): Shift
    {
        $tenantId = session('tenant_id');

        $existingOpenShift = Shift::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->whereNull('closed_at')
            ->first();

        if ($existingOpenShift) {
            throw new \Exception('You already have an open shift. Close it first.');
        }

        return Shift::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
        ]);
    }

    public function close(Shift $shift, int $closingCash): array
    {
        if (!$shift->isOpen()) {
            throw new \Exception('This shift is already closed.');
        }

        $cashPayments = Payment::join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.shift_id', $shift->id)
            ->where('payments.method', 'cash')
            ->where('payments.status', 'paid')
            ->sum('payments.amount');

        $cashRefunds = 0;

        $expectedCash = $shift->opening_cash + $cashPayments - $cashRefunds;
        $difference = $closingCash - $expectedCash;

        DB::beginTransaction();

        try {
            $shift->update([
                'closed_at' => now(),
                'closing_cash' => $closingCash,
                'expected_cash' => $expectedCash,
            ]);

            DB::commit();

            return [
                'shift' => $shift->fresh(),
                'cash_payments' => $cashPayments,
                'cash_refunds' => $cashRefunds,
                'expected_cash' => $expectedCash,
                'difference' => $difference,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getCurrentShift(int $userId): ?Shift
    {
        return Shift::where('tenant_id', session('tenant_id'))
            ->where('user_id', $userId)
            ->whereNull('closed_at')
            ->first();
    }

    public function getShiftSummary(Shift $shift): array
    {
        $orders = $shift->orders;

        $totalTransactions = $orders->count();
        $totalRevenue = $orders->sum('net_amount');

        $paymentsByMethod = Payment::join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.shift_id', $shift->id)
            ->where('payments.status', 'paid')
            ->selectRaw('payments.method, SUM(payments.amount) as total')
            ->groupBy('payments.method')
            ->pluck('total', 'method')
            ->toArray();

        $cashPayments = $paymentsByMethod['cash'] ?? 0;
        $cashRefunds = 0; // TODO: Implement refunds logic if applicable
        $expectedCash = $shift->opening_cash + $cashPayments - $cashRefunds;

        return [
            'shift' => $shift,
            'total_transactions' => $totalTransactions,
            'total_revenue' => $totalRevenue,
            'payments_by_method' => $paymentsByMethod,
            'expected_cash' => $expectedCash,
            'cash_difference' => $shift->cash_difference,
        ];
    }
}
