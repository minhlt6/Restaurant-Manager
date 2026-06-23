<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderDetailController extends Controller
{
    public function index(): JsonResponse|View
    {
        $orderDetails = OrderDetail::with(['order', 'item'])->latest()->get();

        return request()->expectsJson()
            ? response()->json($orderDetails)
            : redirect()->route('dashboard');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = \App\Models\Item::find($validated['item_id']);
        $validated['price'] = $item->price;

        $existingDetail = OrderDetail::where('order_id', $validated['order_id'])
            ->where('item_id', $validated['item_id'])
            ->first();

        if ($existingDetail) {
            $existingDetail->quantity += $validated['quantity'];
            $existingDetail->save();
            $orderDetail = $existingDetail;
        } else {
            $orderDetail = OrderDetail::create($validated);
        }

        return $request->expectsJson()
            ? response()->json($orderDetail, 201)
            : redirect()->route('order-details.index');
    }

    public function update(Request $request, OrderDetail $orderDetail): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $orderDetail->update($validated);

        return $request->expectsJson()
            ? response()->json($orderDetail)
            : redirect()->route('order-details.index');
    }

    public function destroy(Request $request, OrderDetail $orderDetail): JsonResponse|RedirectResponse
    {
        $orderDetail->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Deleted'])
            : redirect()->route('order-details.index');
    }

    public function show(OrderDetail $orderDetail): JsonResponse
    {
        return response()->json($orderDetail->load(['order', 'item']));
    }

    public function create(): never
    {
        abort(404);
    }

    public function edit(OrderDetail $orderDetail): never
    {
        abort(404);
    }
}
