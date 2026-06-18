@extends('layouts.app')

@section('title', 'Báo cáo')
@section('header', 'Báo cáo & Thống kê')
@section('breadcrumb', 'Trang chủ / Báo cáo')

@section('content')
<div class="mt-4 space-y-6">
    {{-- Period filter --}}
    <div class="card-glass rounded-2xl p-4 flex flex-wrap gap-2 items-center">
        <span class="text-sm text-slate-400 mr-1">📅 Kỳ báo cáo:</span>
        @foreach(['today' => 'Hôm nay', 'week' => 'Tuần này', 'month' => 'Tháng này', 'year' => 'Năm nay'] as $p => $label)
        <a href="{{ route('reports.index', ['period' => $p]) }}"
           class="px-3 py-1.5 rounded-xl text-xs border transition-all {{ $period === $p ? 'bg-amber-400/15 border-amber-400/50 text-amber-400' : 'border-slate-700 text-slate-400 hover:border-slate-500' }}">
            {{ $label }}
        </a>
        @endforeach
        <form method="GET" action="{{ route('reports.index') }}" class="flex items-center gap-2 ml-2">
            <input type="hidden" name="period" value="custom">
            <input type="date" name="date_from" value="{{ $dateFrom->toDateString() }}" class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-amber-400/50">
            <span class="text-slate-500 text-xs">đến</span>
            <input type="date" name="date_to" value="{{ $dateTo->toDateString() }}" class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-amber-400/50">
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-700 text-slate-300 text-xs hover:bg-slate-600 transition-all">Lọc</button>
        </form>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-slate-400 uppercase tracking-wider">Doanh thu</span>
                <span class="text-xl">💰</span>
            </div>
            <div class="text-2xl font-bold text-green-400">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
            <div class="text-xs text-slate-500 mt-1">Tổng từ đơn đã thanh toán</div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-slate-400 uppercase tracking-wider">Số đơn</span>
                <span class="text-xl">🧾</span>
            </div>
            <div class="text-2xl font-bold text-white">{{ $totalOrders }}</div>
            <div class="text-xs text-slate-500 mt-1">Đơn đã thanh toán</div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-slate-400 uppercase tracking-wider">Trung bình/đơn</span>
                <span class="text-xl">📊</span>
            </div>
            <div class="text-2xl font-bold text-amber-400">{{ number_format($avgOrderValue, 0, ',', '.') }}đ</div>
            <div class="text-xs text-slate-500 mt-1">Giá trị trung bình mỗi đơn</div>
        </div>
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-slate-400 uppercase tracking-wider">Đơn hủy</span>
                <span class="text-xl">🚫</span>
            </div>
            <div class="text-2xl font-bold text-red-400">{{ $cancelledCount }}</div>
            <div class="text-xs text-slate-500 mt-1">Đơn bị hủy trong kỳ</div>
        </div>
    </div>

    <div class="grid xl:grid-cols-2 gap-6">
        {{-- Daily Revenue Chart --}}
        <div class="card-glass rounded-2xl p-5">
            <h2 class="font-semibold text-white mb-4">📈 Doanh thu 7 ngày gần nhất</h2>
            @if($dailyRevenue->isEmpty())
            <div class="text-center py-8 text-slate-500 text-sm">Không có dữ liệu</div>
            @else
            @php $maxRevenue = $dailyRevenue->max('revenue') ?: 1; @endphp
            <div class="space-y-3">
                @foreach($dailyRevenue as $day)
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-slate-400">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</span>
                        <span class="text-amber-400 font-semibold">{{ number_format($day->revenue, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-amber-400 to-orange-500 rounded-full transition-all"
                             style="width: {{ ($day->revenue / $maxRevenue) * 100 }}%"></div>
                    </div>
                    <div class="text-xs text-slate-600 mt-0.5">{{ $day->orders }} đơn</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Top Items --}}
        <div class="card-glass rounded-2xl p-5">
            <h2 class="font-semibold text-white mb-4">🥘 Top món bán chạy</h2>
            @if($topItems->isEmpty())
            <div class="text-center py-8 text-slate-500 text-sm">Không có dữ liệu</div>
            @else
            @php $maxQty = $topItems->max('total_qty') ?: 1; @endphp
            <div class="space-y-3">
                @foreach($topItems as $i => $detail)
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-lg {{ $i < 3 ? 'bg-amber-400/20 text-amber-400' : 'bg-slate-700 text-slate-400' }} text-xs font-bold flex items-center justify-center shrink-0">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300 truncate">{{ $detail->item->name ?? 'N/A' }}</span>
                            <span class="text-amber-400 font-semibold shrink-0 ml-2">{{ $detail->total_qty }} phần</span>
                        </div>
                        <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $i === 0 ? 'bg-amber-400' : ($i === 1 ? 'bg-orange-400' : ($i === 2 ? 'bg-yellow-500' : 'bg-slate-500')) }}"
                                 style="width: {{ ($detail->total_qty / $maxQty) * 100 }}%"></div>
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ number_format($detail->total_revenue, 0, ',', '.') }}đ</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Top Employees --}}
    <div class="card-glass rounded-2xl p-5">
        <h2 class="font-semibold text-white mb-4">👤 Hiệu suất nhân viên</h2>
        @if($topEmployees->isEmpty())
        <div class="text-center py-8 text-slate-500 text-sm">Không có dữ liệu</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800">
                        <th class="pb-3 text-left text-xs text-slate-400 uppercase tracking-wider">Xếp hạng</th>
                        <th class="pb-3 text-left text-xs text-slate-400 uppercase tracking-wider">Nhân viên</th>
                        <th class="pb-3 text-center text-xs text-slate-400 uppercase tracking-wider">Số đơn</th>
                        <th class="pb-3 text-right text-xs text-slate-400 uppercase tracking-wider">Doanh thu</th>
                        <th class="pb-3 text-right text-xs text-slate-400 uppercase tracking-wider">TB/đơn</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($topEmployees as $i => $emp)
                    <tr class="hover:bg-slate-800/30">
                        <td class="py-3">
                            <span class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-xs font-bold {{ $i === 0 ? 'bg-amber-400 text-slate-950' : ($i === 1 ? 'bg-slate-500 text-white' : 'bg-slate-700 text-slate-300') }}">
                                {{ $i + 1 }}
                            </span>
                        </td>
                        <td class="py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-slate-950 font-bold text-xs">
                                    {{ mb_substr($emp->employee->name ?? 'N', 0, 1) }}
                                </div>
                                <span class="text-white font-medium">{{ $emp->employee->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="py-3 text-center text-slate-300">{{ $emp->order_count }}</td>
                        <td class="py-3 text-right font-bold text-green-400">{{ number_format($emp->total_revenue, 0, ',', '.') }}đ</td>
                        <td class="py-3 text-right text-slate-400">{{ number_format($emp->order_count > 0 ? $emp->total_revenue / $emp->order_count : 0, 0, ',', '.') }}đ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
