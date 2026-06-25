<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Tạo lịch đặt bàn trước, khóa bàn → STATUS_RESERVED
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dining_table_id'  => ['required', 'exists:dining_tables,id'],
            'customer_id'      => ['nullable', 'exists:customers,id'],
            'customer_name'    => ['nullable', 'string', 'max:100'],
            'customer_phone'   => ['nullable', 'string', 'max:20'],
            'reservation_time' => ['required', 'date', 'after:now'],
            'note'             => ['nullable', 'string', 'max:500'],
        ], [
            'dining_table_id.required'  => 'Vui lòng chọn bàn.',
            'reservation_time.required' => 'Vui lòng chọn thời gian đến.',
            'reservation_time.after'    => 'Thời gian đặt bàn phải là tương lai.',
        ]);

        $table = DiningTable::findOrFail($validated['dining_table_id']);

        if ($table->status !== DiningTable::STATUS_FREE) {
            return back()->with('error', 'Bàn này hiện không còn trống, không thể đặt trước!');
        }

        $customerId = $validated['customer_id'] ?? null;
        $customerName = $validated['customer_name'] ?? null;
        $customerPhone = $validated['customer_phone'] ?? null;

        // Nếu khách cũ được chọn
        if ($customerId) {
            $customer = Customer::find($customerId);
            $customerName = $customer->name;
            $customerPhone = $customer->phone;
        } 
        // Nếu nhập khách mới
        elseif ($customerName && $customerPhone) {
            // Tìm xem SĐT này đã có chưa
            $customer = Customer::where('phone', $customerPhone)->first();
            if (!$customer) {
                // Tự động tạo khách mới
                $customer = Customer::create([
                    'name' => $customerName,
                    'phone' => $customerPhone,
                    'gender' => 'Khác', // Cấp mặc định vì đặt bàn không hỏi giới tính
                    'customer_group_id' => 1, // Gán tạm nhóm mặc định (thường ID=1 là Khách vãng lai/Cơ bản)
                ]);
            }
            $customerId = $customer->id;
            // Dùng tên mới nhất từ DB nếu đã có
            $customerName = $customer->name;
        } else {
            return back()->with('error', 'Vui lòng chọn khách hàng hoặc nhập thông tin khách mới!');
        }

        Reservation::create([
            'dining_table_id'  => $table->id,
            'customer_id'      => $customerId,
            'customer_name'    => $customerName,
            'customer_phone'   => $customerPhone,
            'reservation_time' => $validated['reservation_time'],
            'note'             => $validated['note'] ?? null,
            'status'           => Reservation::STATUS_WAITING,
        ]);

        $table->update(['status' => DiningTable::STATUS_RESERVED]);

        return redirect()->route('dining-tables.index')
            ->with('success', 'Đặt bàn trước thành công! Bàn đã được khóa.');
    }

    /**
     * Hủy lịch đặt bàn → giải phóng bàn về STATUS_FREE
     */
    public function cancel(Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== Reservation::STATUS_WAITING) {
            return back()->with('error', 'Lịch đặt bàn này đã được xử lý, không thể hủy.');
        }

        $reservation->update([
            'status' => Reservation::STATUS_CANCELLED,
        ]);

        $reservation->diningTable->update(['status' => DiningTable::STATUS_FREE]);

        return redirect()->route('dining-tables.index')
            ->with('success', 'Đã hủy lịch đặt bàn. Bàn đã được giải phóng.');
    }

    /**
     * Khách đến nhận bàn → tạo Order mới, chuyển bàn sang STATUS_SERVING
     */
    public function receive(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== Reservation::STATUS_WAITING) {
            return back()->with('error', 'Lịch đặt bàn này đã được xử lý rồi.');
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            // customer_id không lấy từ form receive nữa mà lấy từ reservation
        ]);

        $table = $reservation->diningTable;

        // Cập nhật lịch đặt thành đã nhận bàn
        $reservation->update(['status' => Reservation::STATUS_RECEIVED]);

        // Tạo hóa đơn mới cho lượt ăn này
        $order = Order::create([
            'dining_table_id' => $table->id,
            'employee_id'     => $validated['employee_id'],
            'customer_id'     => $reservation->customer_id, // Lấy customer_id từ reservation
            'total_price'     => 0,
            'status'          => Order::STATUS_UNPAID,
            'time_in'         => now(),
        ]);

        // Chuyển trạng thái bàn sang Đang phục vụ
        $table->update(['status' => DiningTable::STATUS_SERVING]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Khách đã nhận bàn! Bắt đầu gọi món.');
    }
}
