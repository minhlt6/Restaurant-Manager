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
            'extra_tables'    => ['nullable', 'array'],
            'extra_tables.*'  => ['exists:dining_tables,id'],
            'deposit'         => ['nullable', 'numeric', 'min:0'],
        ]);

        // Check wholesale validation
        if ($validated['customer_id']) {
            $customer = Customer::with('customerGroup')->find($validated['customer_id']);
            if ($customer && str_contains(strtolower($customer->customerGroup->name ?? ''), 'sỉ')) {
                $request->validate([
                    'extra_tables' => ['required', 'array', 'min:4'],
                    'deposit'      => ['required', 'numeric', 'min:1'],
                ], [
                    'extra_tables.required' => 'Khách sỉ phải chọn thêm bàn (tối thiểu 4 bàn phụ + 1 bàn chính = 5 bàn).',
                    'extra_tables.min'      => 'Vui lòng chọn thêm ít nhất 4 bàn nữa.',
                    'deposit.required'      => 'Khách sỉ phải có tiền đặt cọc.',
                    'deposit.min'           => 'Tiền đặt cọc phải lớn hơn 0.',
                ]);
            }
        }

        $validated['total_price'] = $validated['deposit'] ?? 0;
        $validated['time_in']     = $validated['time_in'] ?? now();
        $validated['tables_count'] = isset($validated['extra_tables']) ? count($validated['extra_tables']) + 1 : 1;
        
        $extraTables = $validated['extra_tables'] ?? [];
        unset($validated['extra_tables'], $validated['deposit']);

        // Check if any of the tables are already in use
        $allTableIds = array_merge([$validated['dining_table_id']], $extraTables);
        $busyTables = DiningTable::whereIn('id', $allTableIds)
            ->where('status', '!=', DiningTable::STATUS_FREE)
            ->count();
            
        if ($busyTables > 0) {
            return back()->with('error', 'Một hoặc nhiều bàn đã được sử dụng hoặc đặt trước!');
        }

        $order = Order::create($validated);
        
        if (!empty($extraTables)) {
            $order->extraDiningTables()->attach($extraTables);
        }

        // Update table status to serving (main table + extra tables)
        DiningTable::whereIn('id', $allTableIds)->update(['status' => DiningTable::STATUS_SERVING]);

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

        $oldTableId = $order->dining_table_id;
        $oldStatus  = $order->status;
        $newTableId = (int)$validated['dining_table_id'];
        $newStatus  = (int)$validated['status'];

        // If transferring to a new table, ensure it's free
        if ($oldTableId !== $newTableId && $newStatus === Order::STATUS_UNPAID) {
            $busy = DiningTable::where('id', $newTableId)->where('status', '!=', DiningTable::STATUS_FREE)->exists();
            if ($busy) {
                if ($request->expectsJson()) return response()->json(['message' => 'Bàn mới đã được sử dụng!'], 422);
                return back()->with('error', 'Bàn mới đã được sử dụng hoặc đặt trước!');
            }
        }

        // Free the old table if order is active and table changed
        if ($oldTableId !== $newTableId && $oldStatus === Order::STATUS_UNPAID) {
            DiningTable::where('id', $oldTableId)->update(['status' => DiningTable::STATUS_FREE]);
        }

        $order->update($validated);

        // Adjust states based on the new status
        if ($newStatus === Order::STATUS_UNPAID) {
            DiningTable::where('id', $newTableId)->update(['status' => DiningTable::STATUS_SERVING]);
            if ($order->extraDiningTables()->count() > 0) {
                $order->extraDiningTables()->update(['status' => DiningTable::STATUS_SERVING]);
            }
        } else {
            DiningTable::where('id', $newTableId)->update(['status' => DiningTable::STATUS_FREE]);
            if ($order->extraDiningTables()->count() > 0) {
                $order->extraDiningTables()->update(['status' => DiningTable::STATUS_FREE]);
            }
        }

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

        // Free the table(s)
        $order->diningTable->update(['status' => DiningTable::STATUS_FREE]);
        if ($order->extraDiningTables()->count() > 0) {
            $order->extraDiningTables()->update(['status' => DiningTable::STATUS_FREE]);
        }

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
        if ($order->extraDiningTables()->count() > 0) {
            $order->extraDiningTables()->update(['status' => DiningTable::STATUS_FREE]);
        }

        return redirect()->route('dining-tables.index')->with('success', 'Đã hủy hóa đơn và giải phóng bàn.');
    }

    public function destroy(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        // Free tables if order is unpaid
        if ($order->status === Order::STATUS_UNPAID) {
            $order->diningTable->update(['status' => DiningTable::STATUS_FREE]);
            if ($order->extraDiningTables()->count() > 0) {
                $order->extraDiningTables()->update(['status' => DiningTable::STATUS_FREE]);
            }
        }

        // Clean up relations to prevent orphan data
        $order->orderDetails()->delete();
        $order->extraDiningTables()->detach();

        $order->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Deleted'])
            : redirect()->route('orders.index')->with('success', 'Đã xóa hóa đơn!');
    }

    public function create(): never { abort(404); }
    public function edit(Order $order): never { abort(404); }
}
