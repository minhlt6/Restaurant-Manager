<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->input('period', 'today');
        $dateFrom = match($period) {
            'today'   => now()->startOfDay(),
            'week'    => now()->startOfWeek(),
            'month'   => now()->startOfMonth(),
            'year'    => now()->startOfYear(),
            'custom'  => now()->parse($request->input('date_from', now()->toDateString()))->startOfDay(),
            default   => now()->startOfDay(),
        };
        $dateTo = $period === 'custom'
            ? now()->parse($request->input('date_to', now()->toDateString()))->endOfDay()
            : now()->endOfDay();

        // Revenue summary
        $paidOrders = Order::where('status', Order::STATUS_PAID)
            ->whereBetween('updated_at', [$dateFrom, $dateTo])
            ->get();

        $totalRevenue   = $paidOrders->sum('total_price');
        $totalOrders    = $paidOrders->count();
        $avgOrderValue  = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $cancelledCount = Order::where('status', Order::STATUS_CANCELLED)
            ->whereBetween('updated_at', [$dateFrom, $dateTo])
            ->count();

        // Top selling items
        $topItems = OrderDetail::with('item')
            ->whereHas('order', fn($q) => $q->where('status', Order::STATUS_PAID)->whereBetween('updated_at', [$dateFrom, $dateTo]))
            ->selectRaw('item_id, SUM(quantity) as total_qty, SUM(price * quantity) as total_revenue')
            ->groupBy('item_id')
            ->orderByDesc('total_qty')
            ->take(8)
            ->get();

        // Top employees
        $topEmployees = Order::with('employee')
            ->where('status', Order::STATUS_PAID)
            ->whereBetween('updated_at', [$dateFrom, $dateTo])
            ->selectRaw('employee_id, COUNT(*) as order_count, SUM(total_price) as total_revenue')
            ->groupBy('employee_id')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        // Revenue by day (last 7 days for chart)
        $dailyRevenue = Order::where('status', Order::STATUS_PAID)
            ->where('updated_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw("DATE(updated_at) as date, SUM(total_price) as revenue, COUNT(*) as orders")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.index', compact(
            'period', 'totalRevenue', 'totalOrders', 'avgOrderValue', 'cancelledCount',
            'topItems', 'topEmployees', 'dailyRevenue', 'dateFrom', 'dateTo'
        ));
    }
}
