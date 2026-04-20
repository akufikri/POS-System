<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnItem as ReturnItemModel;
use App\Models\ReturnModel;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function __construct(private PaymentService $paymentService) {}

    public function createReturn(
        Order $order,
        int $userId,
        string $reason,
        array $itemsToReturn
    ): ReturnModel {
        if ($order->isRefunded()) {
            throw new \Exception('This order has already been refunded.');
        }

        $orderCreatedAt = $order->created_at;
        $hoursSinceOrder = $orderCreatedAt->diffInHours(now());

        if ($hoursSinceOrder > 24) {
            throw new \Exception('Returns are only allowed within 24 hours of purchase.');
        }

        $refundAmount = 0;

        DB::beginTransaction();

        try {
            $return = ReturnModel::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'user_id' => $userId,
                'reason' => $reason,
                'status' => 'approved',
                'refund_amount' => 0,
            ]);

            foreach ($itemsToReturn as $itemData) {
                $orderItem = OrderItem::where('order_id', $order->id)
                    ->where('id', $itemData['order_item_id'])
                    ->first();

                if (!$orderItem) {
                    throw new \Exception("Order item not found.");
                }

                $returnQty = $itemData['quantity'];
                if ($returnQty > $orderItem->quantity) {
                    throw new \Exception("Cannot return more than purchased quantity for item: {$orderItem->product_name}");
                }

                $itemAmount = $orderItem->unit_price * $returnQty;

                ReturnItemModel::create([
                    'return_id' => $return->id,
                    'order_item_id' => $orderItem->id,
                    'quantity' => $returnQty,
                    'amount' => $itemAmount,
                ]);

                $refundAmount += $itemAmount;
            }

            $return->update(['refund_amount' => $refundAmount]);

            if ($refundAmount > 0) {
                $this->processRefund($order, $return, $refundAmount);
            }

            $order->update(['payment_status' => 'refunded']);

            DB::commit();

            return $return->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function processRefund(Order $order, ReturnModel $return, int $amount): void
    {
        $paidPayments = $order->payments()->where('status', 'paid')->get();

        $remainingToRefund = $amount;

        foreach ($paidPayments as $payment) {
            if ($remainingToRefund <= 0) {
                break;
            }

            if ($payment->isDigital()) {
                $refundAmount = min($remainingToRefund, $payment->amount);

                try {
                    $refundResult = $this->paymentService->refund($payment, $refundAmount);

                    $return->update([
                        'midtrans_refund_id' => $refundResult['refund_key'] ?? null,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Midtrans refund failed', [
                        'payment_id' => $payment->id,
                        'return_id' => $return->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }

                $remainingToRefund -= $refundAmount;
            }
        }
    }

    public function getReturnsForTenant(int $tenantId, array $filters = [])
    {
        $query = ReturnModel::with(['order', 'user', 'items.orderItem'])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate(20);
    }
}
