@extends('layouts.app')

@section('title', 'Danh mục món')
@section('header', 'Danh mục món')
@section('breadcrumb', 'Trang chủ / Danh mục món')

@section('header-actions')
<button onclick="openModal()" class="btn-primary px-4 py-2 rounded-xl text-slate-950 font-semibold text-sm">+ Thêm danh mục</button>
@endsection

@section('content')
<div class="mt-4 grid xl:grid-cols-3 gap-4">
    <div class="card-glass rounded-2xl overflow-hidden xl:col-span-1">
        <div class="px-5 py-4 border-b border-slate-800 font-semibold text-white text-sm">📂 Danh sách danh mục</div>
        <div id="cat-list" class="divide-y divide-slate-800">
            @forelse($categories as $cat)
            <div class="flex items-center justify-between px-5 py-3.5 hover:bg-slate-800/30 transition-colors" id="cat-row-{{ $cat->id }}">
                <div>
                    <div class="font-medium text-white">{{ $cat->name }}</div>
                    <div class="text-xs text-slate-500">{{ $cat->items_count }} món</div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick='editCategory(@json($cat))' class="px-2.5 py-1 rounded-lg bg-blue-500/15 text-blue-400 text-xs hover:bg-blue-500/25 transition-all">✏️</button>
                    <button onclick="deleteCategory({{ $cat->id }}, '{{ $cat->name }}')" class="px-2.5 py-1 rounded-lg bg-red-500/15 text-red-400 text-xs hover:bg-red-500/25 transition-all">🗑️</button>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-slate-500 text-sm">Chưa có danh mục nào</div>
            @endforelse
        </div>
    </div>

    <div class="card-glass rounded-2xl overflow-hidden xl:col-span-2">
        <div class="px-5 py-4 border-b border-slate-800 font-semibold text-white text-sm">🥘 Món ăn trong danh mục</div>
        <div class="p-5 text-center text-slate-500 text-sm">
            <div class="text-4xl mb-2">👆</div>
            Chọn danh mục để xem các món trong danh mục đó. Quản lý thực đơn ở trang <a href="{{ route('items.index') }}" class="text-amber-400 hover:underline">Thực đơn</a>.
        </div>
    </div>
</div>

{{-- Modal --}}
<div id="cat-modal" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="if(event.target===this)closeModal()">
    <div class="card-glass rounded-3xl w-full max-w-md mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white" id="modal-title">Thêm danh mục</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <form id="cat-form" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Tên danh mục *</label>
                <input type="text" id="f-name" required maxlength="50" placeholder="VD: Lẩu & Nướng"
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
            </div>
            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-slate-950 font-bold text-sm">Lưu</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const headers = {
        'Content-Type':'application/json','Accept':'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    };
    let editingId = null;

    function openModal() {
        editingId = null;
        document.getElementById('modal-title').textContent = 'Thêm danh mục';
        document.getElementById('f-name').value = '';
        document.getElementById('cat-modal').classList.remove('hidden');
        document.getElementById('cat-modal').classList.add('flex');
    }

    function editCategory(cat) {
        editingId = cat.id;
        document.getElementById('modal-title').textContent = 'Sửa danh mục';
        document.getElementById('f-name').value = cat.name;
        document.getElementById('cat-modal').classList.remove('hidden');
        document.getElementById('cat-modal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('cat-modal').classList.add('hidden');
        document.getElementById('cat-modal').classList.remove('flex');
    }

    document.getElementById('cat-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('f-name').value.trim();
        if (!name) return;

        const url    = editingId ? `/categories/${editingId}` : '/categories';
        const method = editingId ? 'PUT' : 'POST';

        const res = await fetch(url, { method, headers, body: JSON.stringify({ name }) });
        if (res.ok) { closeModal(); location.reload(); }
        else { const d = await res.json(); alert(Object.values(d.errors||{}).flat().join('\n')); }
    });

    async function deleteCategory(id, name) {
        if (!confirm(`Xóa danh mục "${name}"?`)) return;
        const res = await fetch(`/categories/${id}`, { method: 'DELETE', headers });
        if (res.ok) { document.getElementById('cat-row-'+id)?.remove(); }
        else { alert('Không thể xóa! Có thể danh mục đang có món ăn.'); }
    }
</script>
@endpush
