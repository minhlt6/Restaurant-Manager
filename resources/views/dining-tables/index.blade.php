@extends('layouts.app')

@section('title', 'Sơ đồ bàn')
@section('header', 'Sơ đồ bàn')
@section('breadcrumb', 'Trang chủ / Sơ đồ bàn')

@section('header-actions')
<div class="flex items-center gap-2 text-xs">
    <span class="flex items-center gap-1.5 px-2 py-1 rounded-lg table-status-free">⬤ Trống ({{ $freeTables }})</span>
    <span class="flex items-center gap-1.5 px-2 py-1 rounded-lg table-status-serving">⬤ Đang phục vụ ({{ $servingTables }})</span>
    <span class="flex items-center gap-1.5 px-2 py-1 rounded-lg table-status-reserved">⬤ Đặt trước ({{ $reservedTables }})</span>
</div>
@endsection

@section('content')
<div class="mt-4">
    {{-- Legend / Filter --}}
    <div class="mb-5 flex items-center gap-3 flex-wrap">
        <button onclick="filterTables('all')"     id="filter-all"      class="filter-btn active-filter px-4 py-2 rounded-xl text-sm border transition-all">Tất cả ({{ $tables->count() }})</button>
        <button onclick="filterTables('free')"    id="filter-free"     class="filter-btn px-4 py-2 rounded-xl text-sm border border-slate-700 text-slate-400 transition-all">🟢 Trống</button>
        <button onclick="filterTables('serving')" id="filter-serving"  class="filter-btn px-4 py-2 rounded-xl text-sm border border-slate-700 text-slate-400 transition-all">🔴 Đang phục vụ</button>
    </div>

    {{-- Table Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4" id="tables-grid">
        @foreach($tables as $table)
        @php
            $statusClass = match($table->status) {
                0 => 'table-status-free',
                1 => 'table-status-serving',
                2 => 'table-status-reserved',
                default => 'table-status-free'
            };
            $statusLabel = match($table->status) {
                0 => 'Trống',
                1 => 'Đang phục vụ',
                2 => 'Đặt trước',
                default => 'N/A'
            };
            $statusData = match($table->status) { 0 => 'free', 1 => 'serving', 2 => 'reserved', default => 'free' };
            $activeOrder = $table->orders->where('status', 0)->first() ?? $table->extraOrders->where('status', 0)->first();
        @endphp
        <div class="table-card card-glass rounded-2xl p-5 cursor-pointer transition-all hover:-translate-y-0.5 hover:shadow-lg fade-in" data-status="{{ $statusData }}"
            onclick="handleTableClick({{ $table->id }}, {{ $table->status }}, {{ $activeOrder ? $activeOrder->id : 'null' }})">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="font-bold text-white text-lg leading-tight">{{ $table->name }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Sức chứa: {{ $table->capacity }} người</div>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-lg font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="flex items-center justify-between">
                @if($table->status === 1 && $activeOrder)
                <div class="text-xs text-slate-400">
                    <div>Vào: {{ $activeOrder->time_in ? $activeOrder->time_in->format('H:i') : '--:--' }}</div>
                    <div class="text-amber-400 font-semibold">{{ number_format($activeOrder->total_price, 0, ',', '.') }}đ</div>
                </div>
                <div class="text-sm">🧾</div>
                @elseif($table->status === 0)
                <div class="text-xs text-slate-500">Sẵn sàng phục vụ</div>
                <div class="text-2xl">🟢</div>
                @else
                <div class="text-xs text-amber-400">Đã đặt trước</div>
                <div class="text-2xl">🟡</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal: Mở bàn mới --}}
<div id="modal-open-table" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="closeOpenModal(event)">
    <div class="card-glass rounded-3xl w-full max-w-lg mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white">🍽️ Mở bàn mới</h3>
            <button onclick="closeOpenModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>

        <form method="POST" action="{{ route('orders.store') }}" id="open-table-form">
            @csrf
            <input type="hidden" name="dining_table_id" id="ot-table-id">
            <input type="hidden" name="employee_id" value="{{ session('employee.id') }}">
            <input type="hidden" name="status" value="0">
            <input type="hidden" name="time_in" id="ot-time-in">

            <div class="space-y-4">
                <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700">
                    <div class="text-sm text-slate-400">Bàn được chọn</div>
                    <div class="text-xl font-bold text-white mt-1" id="ot-table-name">--</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Khách hàng <span class="text-slate-500">(tùy chọn)</span></label>
                    <input type="text" id="ot-customer-search" placeholder="🔍 Tìm nhanh theo tên hoặc SĐT..." 
                           class="w-full mb-2 bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2 text-xs text-slate-300 focus:border-amber-400/50 focus:outline-none">
                    <select name="customer_id" id="ot-customer" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                        <option value="">— Khách vãng lai —</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" data-group="{{ $c->customerGroup->name ?? '' }}" data-group-id="{{ $c->customer_group_id }}">
                            {{ $c->name }} ({{ $c->customerGroup->name ?? 'Chưa phân loại' }}) {{ $c->phone ? '· '.$c->phone : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Wholesale warning --}}
                <div id="wholesale-warning" class="hidden p-4 rounded-2xl bg-amber-500/10 border border-amber-500/25 text-amber-400 text-sm">
                    ⚠️ <strong>Khách sỉ / Đặt tiệc</strong>: Yêu cầu tối thiểu 5 bàn.
                    <div class="mt-2">
                        <label class="block text-xs text-amber-300 mb-1">Chọn thêm các bàn khác (ít nhất 4 bàn) *</label>
                        <div class="max-h-32 overflow-y-auto bg-slate-800 border border-amber-500/40 rounded-xl p-2 space-y-1">
                            @foreach($tables->where('status', 0) as $t)
                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-700/50 rounded cursor-pointer text-slate-200 extra-table-label" data-id="{{ $t->id }}">
                                <input type="checkbox" name="extra_tables[]" value="{{ $t->id }}" class="extra-table-checkbox rounded bg-slate-700 border-slate-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-800">
                                {{ $t->name }} ({{ $t->capacity }} người)
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="block text-xs text-amber-300 mb-1">Tiền đặt cọc (VNĐ) *</label>
                        <input type="number" name="deposit" id="ot-deposit" min="0" placeholder="Nhập số tiền đặt cọc"
                            class="w-full bg-slate-800 border border-amber-500/40 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none">
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-3 rounded-xl text-slate-950 font-bold text-sm">
                    🍽️ Mở bàn & Bắt đầu phục vụ
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Đang phục vụ --}}
<div id="modal-serving" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="closeServingModal(event)">
    <div class="card-glass rounded-3xl w-full max-w-sm mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white">🔴 Bàn đang phục vụ</h3>
            <button onclick="closeServingModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <div class="text-center py-4">
            <div class="text-4xl mb-3">🍽️</div>
            <div class="text-xl font-bold text-white" id="sv-table-name">Bàn --</div>
            <div class="text-sm text-slate-400 mt-1">Đang phục vụ</div>
        </div>
        <div class="flex gap-3 mt-4">
            <button onclick="goToOrder()" class="btn-primary flex-1 py-3 rounded-xl text-slate-950 font-semibold text-sm">
                📋 Xem & Gọi thêm món
            </button>
            <button onclick="closeServingModal()" class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm border border-slate-700 transition-all">
                Đóng
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentOrderId = null;

    function handleTableClick(tableId, status, orderId) {
        if (status === 0) {
            // Free: show open modal
            document.getElementById('ot-table-id').value = tableId;
            document.getElementById('ot-time-in').value = new Date().toISOString().slice(0, 19).replace('T', ' ');
            const card = document.querySelector(`[onclick*="handleTableClick(${tableId},"]`);
            document.getElementById('ot-table-name').textContent = card.querySelector('.font-bold.text-white').textContent;
            
            // Hide the current table from extra tables list
            document.querySelectorAll('.extra-table-label').forEach(el => {
                if (el.dataset.id == tableId) {
                    el.style.display = 'none';
                    el.querySelector('input').checked = false;
                } else {
                    el.style.display = 'flex';
                }
            });

            document.getElementById('modal-open-table').classList.remove('hidden');
            document.getElementById('modal-open-table').classList.add('flex');
        } else if (status === 1 && orderId) {
            // Serving: show go to order
            currentOrderId = orderId;
            const card = document.querySelector(`[onclick*="handleTableClick(${tableId},"]`);
            document.getElementById('sv-table-name').textContent = card.querySelector('.font-bold.text-white').textContent;
            document.getElementById('modal-serving').classList.remove('hidden');
            document.getElementById('modal-serving').classList.add('flex');
        }
    }

    function closeOpenModal(event) {
        if (!event || event.target === document.getElementById('modal-open-table')) {
            document.getElementById('modal-open-table').classList.add('hidden');
            document.getElementById('modal-open-table').classList.remove('flex');
            
            // Reset customer search input and options display
            const searchInput = document.getElementById('ot-customer-search');
            if (searchInput) {
                searchInput.value = '';
                const select = document.getElementById('ot-customer');
                for (let opt of select.options) {
                    opt.style.display = "";
                }
            }
        }
    }

    function closeServingModal(event) {
        if (!event || event.target === document.getElementById('modal-serving')) {
            document.getElementById('modal-serving').classList.add('hidden');
            document.getElementById('modal-serving').classList.remove('flex');
        }
    }

    function goToOrder() { window.location.href = `/orders/${currentOrderId}`; }

    // Search customer in select option
    document.getElementById('ot-customer-search')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        const select = document.getElementById('ot-customer');
        for (let opt of select.options) {
            if (opt.value === "") continue; // Skip default option
            const text = opt.textContent.toLowerCase();
            if (text.includes(q)) {
                opt.style.display = "";
            } else {
                opt.style.display = "none";
            }
        }
    });

    // Wholesale customer detection
    document.getElementById('ot-customer').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const group = opt.dataset.group || '';
        const warning = document.getElementById('wholesale-warning');
        if (group.toLowerCase().includes('sỉ')) {
            warning.classList.remove('hidden');
            document.getElementById('ot-deposit').required = true;
        } else {
            warning.classList.add('hidden');
            document.getElementById('ot-deposit').required = false;
            // Uncheck extra tables
            document.querySelectorAll('.extra-table-checkbox').forEach(cb => cb.checked = false);
        }
    });

    // Form validation before submit
    document.getElementById('open-table-form').addEventListener('submit', function(e) {
        const warning = document.getElementById('wholesale-warning');
        if (!warning.classList.contains('hidden')) {
            const checkedBoxes = document.querySelectorAll('.extra-table-checkbox:checked');
            if (checkedBoxes.length < 4) {
                e.preventDefault();
                alert('Khách sỉ vui lòng chọn thêm ít nhất 4 bàn nữa (tổng 5 bàn).');
            }
        }
    });

    // Filter tables
    function filterTables(filter) {
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active-filter', 'bg-amber-400/15', 'border-amber-400/50', 'text-amber-400');
            b.classList.add('border-slate-700', 'text-slate-400');
        });
        const btn = document.getElementById('filter-' + filter);
        btn.classList.add('active-filter', 'bg-amber-400/15', 'border-amber-400/50', 'text-amber-400');
        btn.classList.remove('border-slate-700', 'text-slate-400');

        document.querySelectorAll('.table-card').forEach(card => {
            card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
        });
    }

    // Init filter style
    document.getElementById('filter-all').classList.add('bg-amber-400/15', 'border-amber-400/50', 'text-amber-400');
    document.getElementById('filter-all').classList.remove('border-slate-700', 'text-slate-400');

    // Auto reload every 30s to reflect table status changes
    setTimeout(() => location.reload(), 30000);
</script>
@endpush
