<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function __construct(private ReturnService $returnService) {}

    public function index(Request $request): View
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }

        $filters = [
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $returns = $this->returnService->getReturnsForTenant(session('tenant_id'), $filters);

        return view('page.returns.index', compact('returns', 'filters'));
    }

    public function store(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|integer|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $return = $this->returnService->createReturn(
                $order,
                auth()->id(),
                $request->reason,
                $request->items
            );

            return response()->json([
                'success' => true,
                'return' => $return,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
