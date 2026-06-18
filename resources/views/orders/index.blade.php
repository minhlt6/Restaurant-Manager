@extends('layouts.app')

@section('title', 'Danh sách hóa đơn')
@section('header', 'Hóa đơn')
@section('breadcrumb', 'Trang chủ / Hóa đơn')

@section('content')
<div class="mt-4 space-y-4">
    {{-- Filter --}}
    <div class="card-glass rounded-2xl p-4 flex flex-wrap gap-3 items-center">
        <span class="text-sm text-slate-400">Lọc:</span>
        <a href="{{ route('orders.index') }}" class="px-3 py-1.5 rounded-xl text-xs border transition-all {{ !request('status') ? 'bg-amber-400/15 border-amber-400/50 text-amber-400' : 'border-slate-700 text-slate-400 hover:border-slate-500' }}">Tất cả</a>
        <a href="{{ route('orders.index', ['status' => 0]) }}" class="px-3 py-1.5 rounded-xl text-xs border transition-all {{ request('status') === '0' ? 'bg-red-500/15 border-red-500/25 text-red-400' : 'border-slate-700 text-slate-400 hover:border-slate-500' }}">🔴 Đang phục vụ</a>
        <a href="{{ route('orders.index', ['status' => 1]) }}" class="px-3 py-1.5 rounded-xl text-xs border transition-all {{ request('status') === '1' ? 'bg-green-500/15 border-green-500/25 text-green-400' : 'border-slate-700 text-slate-400 hover:border-slate-500' }}">✅ Đã thanh toán</a>
        <a href="{{ route('orders.index', ['status' => 2]) }}" class="px-3 py-1.5 rounded-xl text-xs border transition-all {{ request('status') === '2' ? 'bg-slate-500/15 border-slate-500/25 text-slate-400' : 'border-slate-700 text-slate-400 hover:border-slate-500' }}">🚫 Đã hủy</a>
    </div>

    {{-- Table --}}
    <div class="card-glass rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Bàn</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Khách hàng</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nhân viên</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Giờ vào</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Tổng tiền</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @php
                    $filteredOrders = request('status') !== null ? $orders->where('status', (int)request('status')) : $orders;
                @endphp
                @forelse($filteredOrders as $order)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-3.5 text-slate-500 font-mono">#{{ $order->id }}</td>
                    <td class="px-5 py-3.5 font-medium text-white">{{ $order->diningTable->name ?? 'N/A' }}</td>
                    <td class="px-5 py-3.5 text-slate-300">
                        {{ $order->customer->name ?? 'Khách vãng lai' }}
                        @if($order->customer?->customerGroup)
                        <span class="ml-1 text-xs text-slate-500">({{ $order->customer->customerGroup->name }})</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-slate-300">{{ $order->employee->name ?? 'N/A' }}</td>
                    <td class="px-5 py-3.5 text-slate-400">{{ $order->time_in ? $order->time_in->format('H:i d/m') : '--' }}</td>
                    <td class="px-5 py-3.5 text-right font-semibold text-amber-400">
                        {{ number_format($order->total_price, 0, ',', '.') }}đ
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium
                            {{ $order->status === 0 ? 'table-status-serving' : ($order->status === 1 ? 'bg-green-500/15 text-green-400' : 'bg-slate-500/15 text-slate-400') }}">
                            {{ $order->status === 0 ? 'Đang phục vụ' : ($order->status === 1 ? 'Đã thanh toán' : 'Đã hủy') }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($order->status === 0)
                        <a href="{{ route('orders.show', $order) }}" class="px-3 py-1.5 rounded-lg bg-amber-400/15 text-amber-400 text-xs hover:bg-amber-400/25 transition-all">
                            Xem & Gọi món
                        </a>
                        @else
                        <a href="{{ route('orders.show', $order) }}" class="px-3 py-1.5 rounded-lg bg-slate-700 text-slate-300 text-xs hover:bg-slate-600 transition-all">
                            Xem chi tiết
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                        <div class="text-4xl mb-2">📭</div>
                        <div>Không có hóa đơn nào</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
