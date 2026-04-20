<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function store(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'method' => 'required|in:cash,qris,gopay,ovo,dana',
            'amount' => 'required|integer|min:1',
        ]);

        try {
            if ($request->method === 'cash') {
                $payment = $this->paymentService->recordCash($order, $request->amount);

                $order->update(['payment_status' => 'paid']);

                $change = $request->amount - $order->net_amount;

                return response()->json([
                    'success' => true,
                    'payment' => $payment,
                    'change' => $change >= 0 ? $change : 0,
                ]);
            } else {
                $result = $this->paymentService->charge($order, $request->method, $request->amount);

                return response()->json([
                    'success' => true,
                    ...$result,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            $success = $this->paymentService->handleWebhook($request->all());

            return response()->json(['success' => $success]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function status(string $transactionId): JsonResponse
    {
        try {
            $status = $this->paymentService->checkStatus($transactionId);

            return response()->json([
                'success' => true,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
