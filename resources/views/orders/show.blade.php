@extends('layouts.app')

@section('title', 'Gọi món - ' . $order->diningTable->name)
@section('header', 'Gọi món — ' . $order->diningTable->name)
@section('breadcrumb', 'Sơ đồ bàn / ' . $order->diningTable->name)

@section('header-actions')
<div class="flex items-center gap-2">
    @if($order->status === 0)
    <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Hủy đơn này?')">
        @csrf @method('PATCH')
        <button class="px-3 py-2 rounded-xl bg-red-500/15 border border-red-500/25 text-red-400 text-sm hover:bg-red-500/25 transition-all">
            🚫 Hủy đơn
        </button>
    </form>
    <form method="POST" action="{{ route('orders.checkout', $order) }}" id="checkout-form">
        @csrf @method('PATCH')
        <button type="button" onclick="showCheckout()" class="btn-primary px-4 py-2 rounded-xl text-slate-950 font-bold text-sm">
            💳 Thanh toán
        </button>
    </form>
    @else
    <span class="px-3 py-1.5 rounded-xl text-sm
        {{ $order->status == 1 ? 'bg-green-500/15 text-green-400 border border-green-500/25' : 'bg-red-500/15 text-red-400 border border-red-500/25' }}">
        {{ $order->status == 1 ? '✅ Đã thanh toán' : '🚫 Đã hủy' }}
    </span>
    @endif
</div>
@endsection

@section('content')
<div class="mt-4 grid xl:grid-cols-3 gap-6">

    {{-- LEFT: Order info + items ordered --}}
    <div class="xl:col-span-1 space-y-4">
        {{-- Order info card --}}
        <div class="card-glass rounded-2xl p-5">
            <h2 class="font-semibold text-white mb-3">📋 Thông tin đơn</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-400">Bàn</span><span class="font-medium text-white">{{ $order->diningTable->name }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Nhân viên</span><span class="text-white">{{ $order->employee->name }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Khách hàng</span>
                    <span class="text-right">
                        <span class="text-white">{{ $order->customer->name ?? 'Khách vãng lai' }}</span>
                        @if($order->customer)
                        <span class="block text-xs text-slate-500">{{ $order->customer->customerGroup->name ?? '' }}</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-slate-400">Vào lúc</span><span class="text-amber-400">{{ $order->time_in ? $order->time_in->format('H:i - d/m/Y') : '--' }}</span></div>
                @if($isWholesale)
                <div class="mt-2 p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-400">
                    🏷️ Khách sỉ — Chiết khấu {{ $discountPct }}%
                </div>
                @endif
            </div>
        </div>

        {{-- Items ordered --}}
        <div class="card-glass rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-white">🛒 Món đã gọi</h2>
                <span id="total-items-count" class="text-xs bg-amber-400/15 text-amber-400 px-2 py-0.5 rounded-lg">0 món</span>
            </div>
            <div id="order-items-list" class="space-y-2 min-h-16">
                @forelse($order->orderDetails as $detail)
                <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-800/60 border border-slate-700/50" id="detail-row-{{ $detail->id }}">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-white truncate">{{ $detail->item->name }}</div>
                        <div class="text-xs text-slate-400">{{ number_format($detail->price, 0, ',', '.') }}đ × {{ $detail->quantity }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-amber-400">{{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}đ</div>
                        @if($order->status === 0)
                        <button onclick="removeDetail({{ $detail->id }})" class="text-xs text-red-400 hover:text-red-300 mt-0.5">Xóa</button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-slate-500 text-sm" id="empty-msg">Chưa có món nào được gọi</div>
                @endforelse
            </div>

            {{-- Total --}}
            <div class="mt-4 pt-4 border-t border-slate-700 space-y-1.5">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Tạm tính</span>
                    <span class="text-white font-semibold" id="subtotal-display">{{ number_format($order->orderDetails->sum(fn($d) => $d->price * $d->quantity), 0, ',', '.') }}đ</span>
                </div>
                @if($isWholesale)
                <div class="flex justify-between text-sm">
                    <span class="text-amber-400">Chiết khấu {{ $discountPct }}%</span>
                    <span class="text-amber-400" id="discount-display">-0đ</span>
                </div>
                @endif
                <div class="flex justify-between text-base font-bold mt-2">
                    <span class="text-white">Tổng cộng</span>
                    <span class="text-amber-400 text-lg" id="total-display">{{ number_format($order->total_price, 0, ',', '.') }}đ</span>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Menu to order --}}
    @if($order->status === 0)
    <div class="xl:col-span-2 card-glass rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-white">🥘 Thực đơn</h2>
            <input type="text" id="menu-search" placeholder="Tìm món..." class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none w-48">
        </div>

        {{-- Category tabs --}}
        <div class="flex gap-2 flex-wrap mb-4" id="category-tabs">
            <button onclick="filterMenu('all')" class="menu-cat-btn active-cat px-3 py-1.5 rounded-lg text-xs font-medium transition-all">Tất cả</button>
            @foreach($categories as $cat)
            <button onclick="filterMenu('{{ $cat->id }}')" data-cat="{{ $cat->id }}" class="menu-cat-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700 transition-all">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>

        {{-- Menu items grid --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[560px] overflow-y-auto pr-1" id="menu-grid">
            @foreach($categories as $cat)
                @foreach($cat->items as $item)
                <div class="menu-item p-3 rounded-xl bg-slate-800/60 border border-slate-700/50 hover:border-amber-400/30 hover:bg-slate-800 transition-all cursor-pointer"
                     data-cat="{{ $cat->id }}" data-name="{{ strtolower($item->name) }}"
                     onclick="addToOrder({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }}, '{{ $item->unit }}')">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-white leading-tight">{{ $item->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $item->unit }}</div>
                            @if($item->description)
                            <div class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $item->description }}</div>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-sm font-bold text-amber-400">{{ number_format($item->price, 0, ',', '.') }}</div>
                            <div class="text-xs text-slate-500">đ/{{ $item->unit }}</div>
                        </div>
                    </div>
                    <button class="mt-2 w-full py-1.5 rounded-lg bg-amber-400/10 text-amber-400 text-xs hover:bg-amber-400/20 transition-all font-medium">
                        + Thêm vào đơn
                    </button>
                </div>
                @endforeach
            @endforeach
        </div>
    </div>
    @else
    <div class="xl:col-span-2 card-glass rounded-2xl p-8 flex items-center justify-center">
        <div class="text-center text-slate-500">
            <div class="text-6xl mb-4">{{ $order->status == 1 ? '✅' : '🚫' }}</div>
            <div class="text-lg font-semibold text-slate-300">{{ $order->status == 1 ? 'Đã thanh toán' : 'Đã hủy' }}</div>
            <div class="text-sm mt-2">{{ $order->status == 1 ? 'Hóa đơn này đã được thanh toán thành công' : 'Hóa đơn này đã bị hủy' }}</div>
            @if($order->time_out)
            <div class="text-xs text-slate-600 mt-1">Lúc {{ $order->time_out->format('H:i - d/m/Y') }}</div>
            @endif
            <a href="{{ route('dining-tables.index') }}" class="inline-block mt-4 btn-primary px-5 py-2.5 rounded-xl text-slate-950 font-semibold text-sm">
                → Về sơ đồ bàn
            </a>
        </div>
    </div>
    @endif
</div>

{{-- Checkout Modal --}}
<div id="modal-checkout" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="if(event.target===this)hideCheckout()">
    <div class="card-glass rounded-3xl w-full max-w-md mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <h3 class="text-lg font-bold text-white mb-5">💳 Xác nhận thanh toán</h3>
        <div class="space-y-3 text-sm">
            <div class="p-4 rounded-2xl bg-slate-800/60 space-y-2">
                <div class="flex justify-between"><span class="text-slate-400">Bàn</span><span class="font-medium text-white">{{ $order->diningTable->name }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Khách</span><span class="text-white">{{ $order->customer->name ?? 'Khách vãng lai' }}</span></div>
                @if($isWholesale)
                <div class="flex justify-between text-amber-400"><span>Chiết khấu {{ $discountPct }}%</span><span id="ck-discount">-0đ</span></div>
                @endif
                <div class="flex justify-between text-base font-bold pt-1 border-t border-slate-700">
                    <span class="text-white">Tổng thanh toán</span>
                    <span class="text-amber-400 text-lg" id="ck-total">0đ</span>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <form method="POST" action="{{ route('orders.checkout', $order) }}" class="flex-1">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-primary w-full py-3 rounded-xl text-slate-950 font-bold">
                        ✅ Xác nhận thanh toán
                    </button>
                </form>
                <button onclick="hideCheckout()" class="flex-1 py-3 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 transition-all">
                    Hủy
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const orderId   = {{ $order->id }};
    const isWholesale = {{ $isWholesale ? 'true' : 'false' }};
    const discountPct = {{ $discountPct }};
    const headers   = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    };

    // Track current subtotal
    let currentSubtotal = {{ $order->orderDetails->sum(fn($d) => $d->price * $d->quantity) }};
    updateTotals();

    function updateTotals() {
        const discount = Math.round(currentSubtotal * discountPct / 100);
        const total    = currentSubtotal - discount;
        const fmt      = v => new Intl.NumberFormat('vi-VN').format(v) + 'đ';

        document.getElementById('subtotal-display').textContent = fmt(currentSubtotal);
        if (isWholesale) {
            const dEl = document.getElementById('discount-display');
            if (dEl) dEl.textContent = '-' + fmt(discount);
            const ckD = document.getElementById('ck-discount');
            if (ckD) ckD.textContent = '-' + fmt(discount);
        }
        document.getElementById('total-display').textContent = fmt(total);
        const ckT = document.getElementById('ck-total');
        if (ckT) ckT.textContent = fmt(total);
    }

    async function addToOrder(itemId, itemName, itemPrice, unit) {
        // Show quick quantity picker
        const qty = parseInt(prompt(`Số lượng "${itemName}":`, '1')) || 1;
        if (qty < 1) return;

        const res = await fetch('/order-details', {
            method: 'POST', headers,
            body: JSON.stringify({ order_id: orderId, item_id: itemId, quantity: qty, price: itemPrice }),
        });

        if (res.ok) {
            const detail = await res.json();
            currentSubtotal += itemPrice * qty;

            // Remove empty msg if present
            const emptyMsg = document.getElementById('empty-msg');
            if (emptyMsg) emptyMsg.remove();

            // Add row
            const list = document.getElementById('order-items-list');
            const row  = document.createElement('div');
            row.className = 'flex items-center gap-2 p-3 rounded-xl bg-slate-800/60 border border-slate-700/50 fade-in';
            row.id = 'detail-row-' + detail.id;
            row.innerHTML = `
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-white truncate">${itemName}</div>
                    <div class="text-xs text-slate-400">${new Intl.NumberFormat('vi-VN').format(itemPrice)}đ × ${qty}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-amber-400">${new Intl.NumberFormat('vi-VN').format(itemPrice * qty)}đ</div>
                    <button onclick="removeDetail(${detail.id}, ${itemPrice * qty})" class="text-xs text-red-400 hover:text-red-300 mt-0.5">Xóa</button>
                </div>`;
            list.appendChild(row);

            // Update count badge
            const badge = document.getElementById('total-items-count');
            badge.textContent = (list.children.length) + ' món';

            updateTotals();
        } else {
            alert('Lỗi khi thêm món. Vui lòng thử lại.');
        }
    }

    async function removeDetail(detailId, amount) {
        if (!confirm('Xóa món này khỏi đơn?')) return;

        const res = await fetch(`/order-details/${detailId}`, { method: 'DELETE', headers });
        if (res.ok) {
            const row = document.getElementById('detail-row-' + detailId);
            if (row) { currentSubtotal -= (amount || 0); row.remove(); }
            updateTotals();

            const list = document.getElementById('order-items-list');
            if (!list.children.length) {
                list.innerHTML = '<div class="text-center py-6 text-slate-500 text-sm" id="empty-msg">Chưa có món nào được gọi</div>';
            }
            document.getElementById('total-items-count').textContent = list.querySelectorAll('[id^="detail-row-"]').length + ' món';
        }
    }

    function showCheckout() {
        updateTotals();
        document.getElementById('modal-checkout').classList.remove('hidden');
        document.getElementById('modal-checkout').classList.add('flex');
    }

    function hideCheckout() {
        document.getElementById('modal-checkout').classList.add('hidden');
        document.getElementById('modal-checkout').classList.remove('flex');
    }

    // Menu filter
    function filterMenu(catId) {
        document.querySelectorAll('.menu-cat-btn').forEach(b => {
            b.classList.remove('active-cat', 'bg-amber-400/15', 'border-amber-400/50', 'text-amber-400');
            b.classList.add('bg-slate-800', 'text-slate-400', 'border-slate-700');
        });
        const btn = catId === 'all'
            ? document.querySelector('.menu-cat-btn')
            : document.querySelector(`.menu-cat-btn[data-cat="${catId}"]`);
        if (btn) {
            btn.classList.add('active-cat', 'bg-amber-400/15', 'border-amber-400/50', 'text-amber-400');
            btn.classList.remove('bg-slate-800', 'text-slate-400', 'border-slate-700');
        }
        document.querySelectorAll('.menu-item').forEach(item => {
            item.style.display = (catId === 'all' || item.dataset.cat === catId) ? '' : 'none';
        });
    }

    // Search
    document.getElementById('menu-search')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.menu-item').forEach(item => {
            item.style.display = item.dataset.name.includes(q) ? '' : 'none';
        });
    });

    // Init active category style
    document.querySelector('.menu-cat-btn')?.classList.add('bg-amber-400/15', 'border-amber-400/50', 'text-amber-400');
    document.querySelector('.menu-cat-btn')?.classList.remove('bg-slate-800', 'text-slate-400', 'border-slate-700');

    // Init count
    const initialCount = document.querySelectorAll('[id^="detail-row-"]').length;
    document.getElementById('total-items-count').textContent = initialCount + ' món';
</script>
@endpush
