@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('breadcrumb', 'Trang chủ')

@section('content')
<div class="space-y-6 mt-2">

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="card-glass rounded-2xl p-5 fade-in">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Doanh thu hôm nay</span>
                <div class="w-9 h-9 rounded-xl bg-green-500/15 flex items-center justify-center text-lg">💰</div>
            </div>
            <div class="text-2xl font-bold text-white">{{ number_format($revenueToday, 0, ',', '.') }}đ</div>
            <div class="text-xs text-slate-500 mt-1">Tổng đơn đã thanh toán hôm nay</div>
        </div>

        <div class="card-glass rounded-2xl p-5 fade-in">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Đơn hôm nay</span>
                <div class="w-9 h-9 rounded-xl bg-blue-500/15 flex items-center justify-center text-lg">🧾</div>
            </div>
            <div class="text-2xl font-bold text-white">{{ $ordersToday }}</div>
            <div class="text-xs text-slate-500 mt-1">Tổng đơn được tạo hôm nay</div>
        </div>

        <div class="card-glass rounded-2xl p-5 fade-in">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Bàn đang phục vụ</span>
                <div class="w-9 h-9 rounded-xl bg-red-500/15 flex items-center justify-center text-lg">🍽️</div>
            </div>
            <div class="text-2xl font-bold text-white">{{ $servingTables }}</div>
            <div class="text-xs text-slate-500 mt-1"><span class="text-green-400">{{ $freeTables }}</span> bàn đang trống</div>
        </div>

        <div class="card-glass rounded-2xl p-5 fade-in">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Khách hàng</span>
                <div class="w-9 h-9 rounded-xl bg-purple-500/15 flex items-center justify-center text-lg">👥</div>
            </div>
            <div class="text-2xl font-bold text-white">{{ $summary['customers'] }}</div>
            <div class="text-xs text-slate-500 mt-1">Tổng khách hàng trong hệ thống</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card-glass rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-slate-300 mb-3">⚡ Thao tác nhanh</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dining-tables.index') }}" class="btn-primary px-4 py-2 rounded-xl text-slate-950 text-sm font-semibold">
                🍽️ Mở bàn mới
            </a>
            <a href="{{ route('customers.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm border border-slate-700 transition-all">
                👥 Quản lý khách
            </a>
            <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm border border-slate-700 transition-all">
                🧾 Xem hóa đơn
            </a>
            @if(session('employee.role') == 1)
            <a href="{{ route('items.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm border border-slate-700 transition-all">
                🥘 Thực đơn
            </a>
            <a href="{{ route('reports.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm border border-slate-700 transition-all">
                📈 Báo cáo
            </a>
            @endif
        </div>
    </div>

    <div class="grid xl:grid-cols-2 gap-6">
        {{-- Active Orders --}}
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-white">🔴 Đơn đang phục vụ</h2>
                <a href="{{ route('dining-tables.index') }}" class="text-xs text-amber-400 hover:text-amber-300">Xem sơ đồ →</a>
            </div>
            @if($activeOrders->isEmpty())
            <div class="text-center py-8 text-slate-500">
                <div class="text-4xl mb-2">✅</div>
                <div class="text-sm">Không có bàn nào đang phục vụ</div>
            </div>
            @else
            <div class="space-y-2">
                @foreach($activeOrders as $order)
                <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 hover:border-amber-400/20 transition-all">
                    <div class="w-9 h-9 rounded-xl bg-red-500/15 flex items-center justify-center text-sm font-bold text-red-400">
                        {{ $order->diningTable->name ?? 'B?' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-white">{{ $order->diningTable->name ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-400 truncate">
                            {{ $order->customer->name ?? 'Khách vãng lai' }} · NV: {{ $order->employee->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-amber-400">{{ $order->time_in ? $order->time_in->format('H:i') : '--:--' }}</div>
                        <div class="text-xs text-slate-500">vào lúc</div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent Paid --}}
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-white">✅ Đơn vừa thanh toán</h2>
                <a href="{{ route('orders.index') }}" class="text-xs text-amber-400 hover:text-amber-300">Xem tất cả →</a>
            </div>
            @if($recentPaid->isEmpty())
            <div class="text-center py-8 text-slate-500">
                <div class="text-4xl mb-2">📭</div>
                <div class="text-sm">Chưa có đơn nào thanh toán hôm nay</div>
            </div>
            @else
            <div class="space-y-2">
                @foreach($recentPaid as $order)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/50 border border-slate-700/50">
                    <div class="w-9 h-9 rounded-xl bg-green-500/15 flex items-center justify-center text-sm">✅</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-white">{{ $order->diningTable->name ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-400">{{ $order->customer->name ?? 'Khách vãng lai' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-green-400">{{ number_format($order->total_price, 0, ',', '.') }}đ</div>
                        <div class="text-xs text-slate-500">{{ $order->updated_at->format('H:i') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Manager stats --}}
    @if(session('employee.role') == 1)
    <div class="card-glass rounded-2xl p-5">
        <h2 class="font-semibold text-white mb-4">📊 Tổng quan hệ thống</h2>
        <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
            @foreach([
                ['Nhân viên', $summary['employees'], '👤', 'employees.index'],
                ['Khách hàng', $summary['customers'], '👥', 'customers.index'],
                ['Món ăn', $summary['items'], '🥘', 'items.index'],
                ['Bàn ăn', $summary['diningTables'], '🪑', 'dining-tables.manage'],
                ['Tổng đơn', $summary['totalOrders'], '🧾', 'orders.index'],
                ['Đã TT', $summary['paidOrders'], '✅', 'orders.index'],
            ] as [$label, $val, $icon, $route])
            <a href="{{ route($route) }}" class="text-center p-4 rounded-xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 hover:border-amber-400/20 transition-all">
                <div class="text-2xl mb-1">{{ $icon }}</div>
                <div class="text-xl font-bold text-white">{{ $val }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ $label }}</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection