@extends('layouts.app')

@section('title', 'Quản lý bàn ăn')
@section('header', 'Quản lý Bàn ăn')
@section('breadcrumb', 'Trang chủ / Quản lý bàn')

@section('header-actions')
<a href="{{ route('dining-tables.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-sm border border-slate-700 hover:bg-slate-700 transition-all mr-2">🗺️ Sơ đồ bàn</a>
<button onclick="openModal()" class="btn-primary px-4 py-2 rounded-xl text-slate-950 font-semibold text-sm">+ Thêm bàn</button>
@endsection

@section('content')
<div class="mt-4 grid md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
    @forelse($tables as $table)
    @php
        $statusClass = match($table->status) { 0 => 'table-status-free', 1 => 'table-status-serving', 2 => 'table-status-reserved', default => 'table-status-free' };
        $statusLabel = match($table->status) { 0 => 'Trống', 1 => 'Đang phục vụ', 2 => 'Đặt trước', default => 'N/A' };
    @endphp
    <div class="card-glass rounded-2xl p-5 fade-in">
        <div class="flex items-center justify-between mb-3">
            <div class="font-bold text-white text-lg">{{ $table->name }}</div>
            <span class="px-2.5 py-1 rounded-lg text-xs {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
        <div class="text-sm text-slate-400 mb-4">
            <span>🪑 Sức chứa: {{ $table->capacity }} người</span>
        </div>
        <div class="flex gap-2">
            <button onclick='editTable(@json($table))' class="flex-1 px-3 py-2 rounded-xl bg-blue-500/15 text-blue-400 text-xs hover:bg-blue-500/25 transition-all">✏️ Sửa</button>
            <form method="POST" action="{{ route('dining-tables.destroy', $table) }}" onsubmit="return confirm('Xóa bàn này?')" class="flex-1">
                @csrf @method('DELETE')
                <button class="w-full px-3 py-2 rounded-xl bg-red-500/15 text-red-400 text-xs hover:bg-red-500/25 transition-all">🗑️ Xóa</button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-4 text-center py-12 text-slate-500">
        <div class="text-4xl mb-2">🪑</div><div>Chưa có bàn nào</div>
    </div>
    @endforelse
</div>

{{-- Modal --}}
<div id="table-modal" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="if(event.target===this)closeModal()">
    <div class="card-glass rounded-3xl w-full max-w-md mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white" id="modal-title">Thêm bàn</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <form id="table-form" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Tên bàn *</label>
                <input type="text" name="name" id="f-name" required maxlength="50" placeholder="VD: Bàn 01, Bàn VIP"
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Sức chứa (người) *</label>
                <input type="number" name="capacity" id="f-capacity" required min="1" max="50"
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Trạng thái</label>
                <select name="status" id="f-status" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                    <option value="0">🟢 Trống</option>
                    <option value="1">🔴 Đang phục vụ</option>
                    <option value="2">🟡 Đặt trước</option>
                </select>
            </div>
            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-slate-950 font-bold text-sm">Lưu bàn</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModal() {
        document.getElementById('modal-title').textContent = 'Thêm bàn';
        document.getElementById('table-form').action = '{{ route("dining-tables.store") }}';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('f-name').value = '';
        document.getElementById('f-capacity').value = '4';
        document.getElementById('f-status').value = '0';
        document.getElementById('table-modal').classList.remove('hidden');
        document.getElementById('table-modal').classList.add('flex');
    }

    function editTable(t) {
        document.getElementById('modal-title').textContent = 'Sửa bàn';
        document.getElementById('table-form').action = `/dining-tables/${t.id}`;
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('f-name').value = t.name;
        document.getElementById('f-capacity').value = t.capacity;
        document.getElementById('f-status').value = t.status;
        document.getElementById('table-modal').classList.remove('hidden');
        document.getElementById('table-modal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('table-modal').classList.add('hidden');
        document.getElementById('table-modal').classList.remove('flex');
    }
</script>
@endpush
