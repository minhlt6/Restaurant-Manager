<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\DiningTable;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        // Revenue today (paid orders)
        $revenueToday = Order::where('status', Order::STATUS_PAID)
            ->whereDate('updated_at', $today)
            ->sum('total_price');

        // Orders today
        $ordersToday = Order::whereDate('created_at', $today)->count();

        // Currently serving tables
        $servingTables = DiningTable::where('status', DiningTable::STATUS_SERVING)->count();
        $freeTables    = DiningTable::where('status', DiningTable::STATUS_FREE)->count();

        // Recent active orders
        $activeOrders = Order::with(['diningTable', 'employee', 'customer'])
            ->where('status', Order::STATUS_UNPAID)
            ->latest()
            ->take(8)
            ->get();

        // Recent 5 paid orders
        $recentPaid = Order::with(['diningTable', 'customer'])
            ->where('status', Order::STATUS_PAID)
            ->latest()
            ->take(5)
            ->get();

        // Summary counts (for manager)
        $summary = [
            'employees'      => Employee::count(),
            'customers'      => Customer::count(),
            'items'          => Item::count(),
            'diningTables'   => DiningTable::count(),
            'totalOrders'    => Order::count(),
            'paidOrders'     => Order::where('status', Order::STATUS_PAID)->count(),
        ];

        return view('dashboard', compact(
            'revenueToday', 'ordersToday', 'servingTables', 'freeTables',
            'activeOrders', 'recentPaid', 'summary'
        ));
    }
}