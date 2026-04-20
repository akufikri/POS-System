<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with('cashier')
            ->orderByDesc('created_at');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('cashier_id')) {
            $query->where('user_id', $request->cashier_id);
        }

        if ($request->filled('search')) {
            $searchTerm = ltrim($request->search, '#ORD-');
            $query->where('id', 'LIKE', "%{$searchTerm}%");
        }

        $perPage = $request->input('per_page', 10);
        $orders = $query->paginate($perPage)->withQueryString();

        return view('page.transactions.index', compact('orders'));
    }

    public function myHistory(Request $request): View
    {
        $query = Order::with('items')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('search')) {
            $searchTerm = ltrim($request->search, '#ORD-');
            $query->where('id', 'LIKE', "%{$searchTerm}%");
        }

        $perPage = $request->input('per_page', 10);
        $orders = $query->paginate($perPage)->withQueryString();

        $todayStats = Order::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->selectRaw('COUNT(*) as tx_count, SUM(total_amount) as revenue')
            ->first();

        return view('page.my-history.index', compact('orders', 'todayStats'));
    }

    public function export(Request $request)
    {
        $query = Order::with('cashier')
            ->orderByDesc('created_at');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->get();
        $format = strtoupper($request->input('format', 'CSV'));
        $extension = strtolower($format);
        $filename = 'transactions_' . now()->format('Y-m-d_His') . '.' . $extension;

        // Handle PDF Format
        if ($format === 'PDF') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.transactions-pdf', [
                'orders' => $orders,
                'date' => $request->date
            ]);
            return $pdf->download($filename);
        }

        $headers = [
            'Content-Type' => $format === 'CSV' || $format === 'EXCEL' ? 'text/csv' : 'text/plain',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Date', 'Time', 'Cashier', 'Total Amount', 'Profit']);

            foreach ($orders as $order) {
                fputcsv($file, [
                    'ORD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT),
                    $order->created_at->format('d/m/Y'),
                    $order->created_at->format('H:i'),
                    $order->cashier->name ?? 'System',
                    $order->total_amount,
                    $order->profit
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load('items', 'cashier');

        return response()->json([
            'id' => $order->id,
            'cashier' => $order->cashier->name,
            'total_amount' => $order->total_amount,
            'total_amount_formatted' => $order->total_amount_formatted,
            'total_cost' => $order->total_cost,
            'profit' => $order->profit,
            'profit_formatted' => 'Rp ' . number_format($order->profit, 0, ',', '.'),
            'notes' => $order->notes,
            'created_at' => $order->created_at->format('d M Y, H:i'),
            'items' => $order->items->map(fn($item) => [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'unit_price_formatted' => 'Rp ' . number_format($item->unit_price, 0, ',', '.'),
                'subtotal' => $item->subtotal,
                'subtotal_formatted' => 'Rp ' . number_format($item->subtotal, 0, ',', '.'),
            ]),
        ]);
    }
}
