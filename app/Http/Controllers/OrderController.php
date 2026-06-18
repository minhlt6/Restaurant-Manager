<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): JsonResponse|View
    {
        $orders = Order::with(['diningTable', 'employee', 'customer', 'orderDetails.item'])->latest()->get();

        if (request()->expectsJson()) {
            return response()->json($orders);
        }

        return view('orders.index', compact('orders'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'dining_table_id' => ['required', 'exists:dining_tables,id'],
            'employee_id'     => ['required', 'exists:employees,id'],
            'customer_id'     => ['nullable', 'exists:customers,id'],
            'status'          => ['required', 'integer', 'in:0,1,2'],
            'time_in'         => ['nullable', 'date'],
            // Wholesale
            'tables_count'    => ['nullable', 'integer', 'min:5'],
            'deposit'         => ['nullable', 'numeric', 'min:0'],
        ]);

        // Check wholesale validation
        if ($validated['customer_id']) {
            $customer = Customer::with('customerGroup')->find($validated['customer_id']);
            if ($customer && str_contains(strtolower($customer->customerGroup->name ?? ''), 'sỉ')) {
                $request->validate([
                    'tables_count' => ['required', 'integer', 'min:5'],
                    'deposit'      => ['required', 'numeric', 'min:1'],
                ], [
                    'tables_count.required' => 'Khách sỉ phải nhập số bàn (tối thiểu 5 bàn).',
                    'tables_count.min'      => 'Số bàn tối thiểu là 5 cho khách sỉ.',
                    'deposit.required'      => 'Khách sỉ phải có tiền đặt cọc.',
                    'deposit.min'           => 'Tiền đặt cọc phải lớn hơn 0.',
                ]);
            }
        }

        $validated['total_price'] = $validated['deposit'] ?? 0;
        $validated['time_in']     = $validated['time_in'] ?? now();
        unset($validated['tables_count'], $validated['deposit']);

        $order = Order::create($validated);

        // Update table status to serving
        DiningTable::where('id', $validated['dining_table_id'])->update(['status' => DiningTable::STATUS_SERVING]);

        if ($request->expectsJson()) {
            return response()->json($order, 201);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Bàn đã được mở! Bắt đầu gọi món.');
    }

    public function show(Order $order): JsonResponse|View
    {
        $order->load(['diningTable', 'employee', 'customer.customerGroup', 'orderDetails.item.category']);

        if (request()->expectsJson()) {
            return response()->json($order);
        }

        $categories = \App\Models\Category::with('items')->get();
        $isWholesale = $order->customer && str_contains(strtolower($order->customer->customerGroup->name ?? ''), 'sỉ');
        $discountPct = $isWholesale ? 10 : 0; // 10% discount for wholesale

        return view('orders.show', compact('order', 'categories', 'isWholesale', 'discountPct'));
    }

    public function update(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'dining_table_id' => ['required', 'exists:dining_tables,id'],
            'employee_id'     => ['required', 'exists:employees,id'],
            'customer_id'     => ['nullable', 'exists:customers,id'],
            'total_price'     => ['nullable', 'numeric', 'min:0'],
            'status'          => ['required', 'integer', 'in:0,1,2'],
            'time_in'         => ['nullable', 'date'],
            'time_out'        => ['nullable', 'date'],
        ]);

        $order->update($validated);

        return $request->expectsJson()
            ? response()->json($order)
            : redirect()->route('orders.index')->with('success', 'Cập nhật hóa đơn thành công!');
    }

    /**
     * Checkout (thanh toán) - custom action
     */
    public function checkout(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_UNPAID) {
            return back()->with('error', 'Hóa đơn này đã được xử lý rồi.');
        }

        $order->load(['orderDetails.item', 'customer.customerGroup']);

        // Calculate subtotal from order details
        $subtotal = $order->orderDetails->sum(fn($d) => $d->price * $d->quantity);

        // Discount for wholesale customers
        $isWholesale = $order->customer && str_contains(strtolower($order->customer->customerGroup->name ?? ''), 'sỉ');
        $discountPct  = $isWholesale ? 10 : 0;
        $discount     = $subtotal * ($discountPct / 100);
        $total        = $subtotal - $discount;

        $order->update([
            'total_price' => $total,
            'status'      => Order::STATUS_PAID,
            'time_out'    => now(),
        ]);

        // Free the table
        $order->diningTable->update(['status' => DiningTable::STATUS_FREE]);

        return redirect()->route('dining-tables.index')->with('success', 'Thanh toán thành công! Bàn đã được giải phóng.');
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_UNPAID) {
            return back()->with('error', 'Không thể hủy hóa đơn này.');
        }

        $order->update(['status' => Order::STATUS_CANCELLED, 'time_out' => now()]);
        $order->diningTable->update(['status' => DiningTable::STATUS_FREE]);

        return redirect()->route('dining-tables.index')->with('success', 'Đã hủy hóa đơn và giải phóng bàn.');
    }

    public function destroy(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $order->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Deleted'])
            : redirect()->route('orders.index')->with('success', 'Đã xóa hóa đơn!');
    }

    public function create(): never { abort(404); }
    public function edit(Order $order): never { abort(404); }
}
