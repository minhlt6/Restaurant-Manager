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
        @php
            $reservation = $table->reservations->first();
        @endphp
        <div class="table-card card-glass rounded-2xl p-5 cursor-pointer transition-all hover:-translate-y-0.5 hover:shadow-lg fade-in" data-status="{{ $statusData }}"
            onclick="handleTableClick({{ $table->id }}, {{ $table->status }}, {{ $activeOrder ? $activeOrder->id : 'null' }}, {{ $reservation ? $reservation->id : 'null' }})">
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
                @elseif($table->status === 2 && $reservation)
                <div class="text-xs text-amber-300">
                    <div>👤 {{ $reservation->customer_name }}</div>
                    <div>📅 {{ $reservation->reservation_time->format('d/m H:i') }}</div>
                </div>
                <div class="text-2xl">🟡</div>
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

                {{-- Divider --}}
                <div class="flex items-center gap-3">
                    <hr class="flex-1 border-slate-700">
                    <span class="text-xs text-slate-500">hoặc</span>
                    <hr class="flex-1 border-slate-700">
                </div>

                <button type="button" onclick="switchToReservationModal()" class="w-full py-3 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-400 font-semibold text-sm transition-all">
                    📅 Đặt bàn trước
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Đặt bàn trước --}}
<div id="modal-reserve" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="closeReserveModal(event)">
    <div class="card-glass rounded-3xl w-full max-w-lg mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white">📅 Đặt bàn trước</h3>
            <button onclick="closeReserveModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>

        <form method="POST" action="{{ route('reservations.store') }}" id="reserve-form">
            @csrf
            <input type="hidden" name="dining_table_id" id="rv-table-id">

            <div class="space-y-4">
                <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700">
                    <div class="text-sm text-slate-400">Bàn được đặt</div>
                    <div class="text-xl font-bold text-white mt-1" id="rv-table-name">--</div>
                </div>

                {{-- Chuyển đổi chế độ Khách Cũ / Khách Mới --}}
                <div class="flex bg-slate-800 rounded-xl p-1 mb-2">
                    <button type="button" id="tab-old-customer" onclick="setReservationMode('old')" class="flex-1 py-2 text-sm font-semibold rounded-lg bg-amber-500 text-slate-950 transition-all">Khách quen</button>
                    <button type="button" id="tab-new-customer" onclick="setReservationMode('new')" class="flex-1 py-2 text-sm font-semibold rounded-lg text-slate-400 hover:text-slate-200 transition-all">Khách mới</button>
                </div>

                {{-- Chế độ: Khách cũ --}}
                <div id="mode-old-customer" class="space-y-3">
                    <label class="block text-sm font-medium text-slate-300">Khách hàng *</label>
                    <input type="text" id="rv-customer-search" placeholder="🔍 Tìm nhanh theo tên hoặc SĐT..." 
                           class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2 text-xs text-slate-300 focus:border-amber-400/50 focus:outline-none mb-2">
                    <select name="customer_id" id="rv-customer" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                        <option value="">— Chọn khách hàng —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-phone="{{ $c->phone }}">{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Chế độ: Khách mới --}}
                <div id="mode-new-customer" class="hidden grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Tên khách *</label>
                        <input type="text" name="customer_name" id="rv-new-name" placeholder="Nguyễn Văn A"
                               class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Số điện thoại *</label>
                        <input type="text" name="customer_phone" id="rv-new-phone" placeholder="0912345678"
                               class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Thời gian dự kiến đến *</label>
                    <input type="datetime-local" name="reservation_time" required id="rv-time"
                           class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Ghi chú <span class="text-slate-500">(tùy chọn)</span></label>
                    <input type="text" name="note" placeholder="Ví dụ: Sinh nhật, yêu cầu đặc biệt..."
                           class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm transition-all">
                    📅 Xác nhận Đặt bàn trước
                </button>
                <button type="button" onclick="switchToOpenModal()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm border border-slate-700 transition-all">
                    ← Quay lại Mở bàn ngay
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

{{-- Modal: Bàn đã đặt trước --}}
<div id="modal-reserved" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="closeReservedModal(event)">
    <div class="card-glass rounded-3xl w-full max-w-sm mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white">🟡 Bàn đã đặt trước</h3>
            <button onclick="closeReservedModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 mb-4 space-y-2">
            <div class="text-xl font-bold text-white" id="rd-table-name">--</div>
            <div class="text-sm text-amber-300">👤 
                <span id="rd-customer-name-text">--</span>
                <a href="#" id="rd-customer-link" class="hidden underline hover:text-amber-200">--</a>
            </div>
            <div class="text-sm text-slate-300">📞 <span id="rd-customer-phone">--</span></div>
            <div class="text-sm text-slate-300">📅 <span id="rd-reservation-time">--</span></div>
            <div class="text-sm text-slate-400 italic" id="rd-note-wrap">💬 <span id="rd-note"></span></div>
        </div>

        {{-- Form nhận bàn --}}
        <form method="POST" id="receive-form" action="">
            @csrf
            @method('POST')
            <input type="hidden" name="employee_id" value="{{ session('employee.id') }}">
            <div class="space-y-2">
                <button type="submit" class="btn-primary w-full py-3 rounded-xl text-slate-950 font-semibold text-sm">
                    ✅ Khách đã đến — Mở bàn
                </button>
            </div>
        </form>

        {{-- Form hủy đặt --}}
        <form method="POST" id="cancel-reservation-form" action="" class="mt-2">
            @csrf
            @method('PATCH')
            <button type="submit"
                    onclick="return confirm('Bạn có chắc muốn hủy lịch đặt bàn này không?')"
                    class="w-full py-2.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-400 text-sm font-medium transition-all">
                ✕ Hủy lịch đặt bàn
            </button>
        </form>

        <button onclick="closeReservedModal()" class="w-full mt-2 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm border border-slate-700 transition-all">
            Đóng
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentOrderId = null;
    let currentReservationId = null;

    function handleTableClick(tableId, status, orderId, reservationId) {
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
        } else if (status === 2 && reservationId) {
            // Reserved: show reservation modal
            currentReservationId = reservationId;
            // Populate data from data attributes on the card
            const card = document.querySelector(`[onclick*="handleTableClick(${tableId},"]`);
            const tableName = card.querySelector('.font-bold.text-white').textContent;
            const infoEls = card.querySelectorAll('.text-xs.text-amber-300 div');
            document.getElementById('rd-table-name').textContent = tableName;
            // Fetch reservation data via inline data
            fetchReservationData(reservationId, tableName);
            document.getElementById('modal-reserved').classList.remove('hidden');
            document.getElementById('modal-reserved').classList.add('flex');
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

    // ── Reservation modal functions ──────────────────────────────────────────

    function closeReserveModal(event) {
        if (!event || event.target === document.getElementById('modal-reserve')) {
            document.getElementById('modal-reserve').classList.add('hidden');
            document.getElementById('modal-reserve').classList.remove('flex');
            document.getElementById('reserve-form').reset();
        }
    }

    function closeReservedModal(event) {
        if (!event || event.target === document.getElementById('modal-reserved')) {
            document.getElementById('modal-reserved').classList.add('hidden');
            document.getElementById('modal-reserved').classList.remove('flex');
        }
    }

    // Switching reservation modes (old/new customer)
    function setReservationMode(mode) {
        const btnOld = document.getElementById('tab-old-customer');
        const btnNew = document.getElementById('tab-new-customer');
        const divOld = document.getElementById('mode-old-customer');
        const divNew = document.getElementById('mode-new-customer');
        
        const selectCustomer = document.getElementById('rv-customer');
        const inputName = document.getElementById('rv-new-name');
        const inputPhone = document.getElementById('rv-new-phone');

        if (mode === 'old') {
            btnOld.classList.add('bg-amber-500', 'text-slate-950');
            btnOld.classList.remove('text-slate-400');
            btnNew.classList.remove('bg-amber-500', 'text-slate-950');
            btnNew.classList.add('text-slate-400');
            
            divOld.classList.remove('hidden');
            divNew.classList.add('hidden');
            divNew.classList.remove('grid');

            // Manage required fields
            selectCustomer.required = true;
            inputName.required = false;
            inputPhone.required = false;
        } else {
            btnNew.classList.add('bg-amber-500', 'text-slate-950');
            btnNew.classList.remove('text-slate-400');
            btnOld.classList.remove('bg-amber-500', 'text-slate-950');
            btnOld.classList.add('text-slate-400');

            divNew.classList.remove('hidden');
            divNew.classList.add('grid');
            divOld.classList.add('hidden');

            // Manage required fields
            selectCustomer.required = false;
            inputName.required = true;
            inputPhone.required = true;
            
            // clear select
            selectCustomer.value = "";
        }
    }

    // Initialize with old customer mode
    setReservationMode('old');

    // Search customer in reservation modal
    document.getElementById('rv-customer-search')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        const select = document.getElementById('rv-customer');
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

    // Switch from "Mở bàn" modal to "Đặt trước" modal
    function switchToReservationModal() {
        const tableId = document.getElementById('ot-table-id').value;
        const tableName = document.getElementById('ot-table-name').textContent;

        // Close open modal
        document.getElementById('modal-open-table').classList.add('hidden');
        document.getElementById('modal-open-table').classList.remove('flex');

        // Populate reserve modal
        document.getElementById('rv-table-id').value = tableId;
        document.getElementById('rv-table-name').textContent = tableName;

        // Set default time to 1 hour from now
        const defaultTime = new Date(Date.now() + 60 * 60 * 1000);
        const localISO = new Date(defaultTime.getTime() - defaultTime.getTimezoneOffset() * 60000)
            .toISOString().slice(0, 16);
        document.getElementById('rv-time').value = localISO;

        document.getElementById('modal-reserve').classList.remove('hidden');
        document.getElementById('modal-reserve').classList.add('flex');
    }

    // Switch back from "Đặt trước" to "Mở bàn"
    function switchToOpenModal() {
        document.getElementById('modal-reserve').classList.add('hidden');
        document.getElementById('modal-reserve').classList.remove('flex');
        document.getElementById('modal-open-table').classList.remove('hidden');
        document.getElementById('modal-open-table').classList.add('flex');
    }

    // Reservation data is embedded in PHP via a JSON map, avoiding extra AJAX calls
    const reservationDataMap = {
        @foreach($tables->where('status', 2) as $t)
        @php $rv = $t->reservations->first(); @endphp
        @if($rv)
        {{ $rv->id }}: {
            name:  "{{ addslashes($rv->customer_name) }}",
            phone: "{{ $rv->customer_phone }}",
            time:  "{{ $rv->reservation_time->format('H:i d/m/Y') }}",
            note:  "{{ addslashes($rv->note ?? '') }}",
            receiveUrl: "{{ route('reservations.receive', $rv->id) }}",
            cancelUrl:  "{{ route('reservations.cancel', $rv->id) }}",
            customerId: "{{ $rv->customer_id ?? '' }}",
            customerUrl: "{{ $rv->customer_id ? route('customers.show', $rv->customer_id) : '' }}"
        },
        @endif
        @endforeach
    };

    function fetchReservationData(reservationId, tableName) {
        const data = reservationDataMap[reservationId];
        if (!data) return;

        document.getElementById('rd-table-name').textContent = tableName;
        
        // Handle customer name display (link vs text)
        const nameText = document.getElementById('rd-customer-name-text');
        const nameLink = document.getElementById('rd-customer-link');
        if (data.customerId) {
            nameText.classList.add('hidden');
            nameLink.classList.remove('hidden');
            nameLink.textContent = data.name;
            nameLink.href = data.customerUrl;
        } else {
            nameText.classList.remove('hidden');
            nameLink.classList.add('hidden');
            nameText.textContent = data.name;
        }

        document.getElementById('rd-customer-phone').textContent = data.phone;
        document.getElementById('rd-reservation-time').textContent = data.time;

        const noteWrap = document.getElementById('rd-note-wrap');
        const noteEl   = document.getElementById('rd-note');
        if (data.note) {
            noteEl.textContent = data.note;
            noteWrap.classList.remove('hidden');
        } else {
            noteWrap.classList.add('hidden');
        }

        document.getElementById('receive-form').action          = data.receiveUrl;
        document.getElementById('cancel-reservation-form').action = data.cancelUrl;
    }

</script>
@endpush
