<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(private ShiftService $shiftService) {}

    public function current(): JsonResponse
    {
        $shift = $this->shiftService->getCurrentShift(auth()->id());

        return response()->json([
            'success' => true,
            'shift' => $shift,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'opening_cash' => 'required|integer|min:0',
        ]);

        try {
            $shift = $this->shiftService->open(auth()->id(), $request->opening_cash);

            return response()->json([
                'success' => true,
                'shift' => $shift,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function close(Request $request, Shift $shift): JsonResponse
    {
        $request->validate([
            'closing_cash' => 'required|integer|min:0',
        ]);

        if ($shift->user_id !== auth()->id() && !auth()->user()->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $summary = $this->shiftService->close($shift, $request->closing_cash);

            return response()->json([
                'success' => true,
                ...$summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function webIndex(): View
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }

        $perPage = request('per_page', 10);
        $shifts = Shift::with('user')
            ->where('tenant_id', session('tenant_id'))
            ->when(request('search'), function($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('opened_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('page.shifts.index', compact('shifts'));
    }

    public function index(): JsonResponse
    {
        if (!auth()->user()->isOwner()) {
            return response()->json(['success' => false], 403);
        }

        $shifts = Shift::with('user')
            ->where('tenant_id', session('tenant_id'))
            ->orderBy('opened_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'shifts' => $shifts,
        ]);
    }

    public function show(Shift $shift): JsonResponse
    {
        if (!auth()->user()->isOwner() && $shift->user_id !== auth()->id()) {
            return response()->json(['success' => false], 403);
        }

        $summary = $this->shiftService->getShiftSummary($shift);

        return response()->json([
            'success' => true,
            ...$summary,
        ]);
    }
}
