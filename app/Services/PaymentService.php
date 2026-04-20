<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class PaymentService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function charge(Order $order, string $method, int $amount): array
    {
        if (!in_array($method, ['qris', 'gopay', 'ovo', 'dana'])) {
            throw new \InvalidArgumentException("Invalid payment method: {$method}");
        }

        $params = [
            'transaction_details' => [
                'order_id' => 'POS-' . $order->id . '-' . time(),
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $order->cashier->name,
                'email' => $order->cashier->email,
            ],
            'enabled_payments' => [$method],
        ];

        try {
            $snap = Snap::createTransaction($params);

            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $method,
                'amount' => $amount,
                'status' => 'pending',
                'midtrans_transaction_id' => $snap->transaction_id,
                'midtrans_response' => $snap,
            ]);

            return [
                'payment_id' => $payment->id,
                'transaction_id' => $snap->transaction_id,
                'redirect_url' => $snap->redirect_url,
                'token' => $snap->token,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans charge failed', [
                'order_id' => $order->id,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function recordCash(Order $order, int $amount): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'method' => 'cash',
            'amount' => $amount,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function handleWebhook(array $payload): bool
    {
        $signatureKey = config('services.midtrans.server_key');
        $orderId = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $grossAmount = $payload['gross_amount'];
        $signature = $payload['signature_key'];

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $signatureKey);

        if ($signature !== $expectedSignature) {
            Log::warning('Invalid webhook signature', ['order_id' => $orderId]);
            return false;
        }

        $transactionId = $payload['transaction_id'];
        $paymentStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        $payment = Payment::where('midtrans_transaction_id', $transactionId)->first();

        if (!$payment) {
            Log::warning('Payment not found for webhook', ['transaction_id' => $transactionId]);
            return false;
        }

        $status = match ($paymentStatus) {
            'capture', 'settlement' => 'paid',
            'deny', 'cancel', 'expire' => 'failed',
            'pending' => 'pending',
            default => 'pending',
        };

        $payment->update([
            'status' => $status,
            'midtrans_response' => $payload,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        if ($status === 'paid') {
            $order = $payment->order;
            $order->update(['payment_status' => 'paid']);
        }

        return true;
    }

    public function checkStatus(string $transactionId): array
    {
        try {
            $status = Transaction::status($transactionId);
            return (array) $status;
        } catch (\Exception $e) {
            Log::error('Midtrans status check failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function refund(Payment $payment, int $amount = null): array
    {
        if (!$payment->isPaid() || !$payment->isDigital()) {
            throw new \InvalidArgumentException('Only paid digital payments can be refunded');
        }

        $params = [
            'refund_key' => 'REFUND-' . $payment->id . '-' . time(),
            'amount' => $amount ?? $payment->amount,
        ];

        try {
            $refund = Transaction::refund($payment->midtrans_transaction_id, $params);

            return (array) $refund;
        } catch (\Exception $e) {
            Log::error('Midtrans refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
