@extends('layouts.app')

@section('title', 'Thực đơn')
@section('header', 'Quản lý Thực đơn')
@section('breadcrumb', 'Trang chủ / Thực đơn')

@section('header-actions')
<button onclick="openModal()" class="btn-primary px-4 py-2 rounded-xl text-slate-950 font-semibold text-sm">+ Thêm món ăn</button>
@endsection

@section('content')
<div class="mt-4 space-y-4">
    <div class="card-glass rounded-2xl p-4 flex items-center gap-3">
        <span class="text-slate-400">🔍</span>
        <input type="text" id="search-input" placeholder="Tìm món ăn..." class="flex-1 bg-transparent text-slate-200 text-sm placeholder-slate-500 focus:outline-none">
        <select id="filter-cat" class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-1.5 text-sm text-slate-300 focus:outline-none">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="card-glass rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tên món</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Danh mục</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Giá</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Đơn vị</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Mô tả</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800" id="items-table-body">
                @forelse($items as $item)
                <tr class="hover:bg-slate-800/30 transition-colors item-row" data-name="{{ strtolower($item->name) }}" data-cat="{{ $item->category_id }}">
                    <td class="px-5 py-3.5 font-medium text-white">{{ $item->name }}</td>
                    <td class="px-5 py-3.5">
                        <span class="px-2.5 py-1 rounded-lg text-xs bg-slate-700 text-slate-300">{{ $item->category->name ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right font-bold text-amber-400">{{ number_format($item->price, 0, ',', '.') }}đ</td>
                    <td class="px-5 py-3.5 text-slate-400">{{ $item->unit }}</td>
                    <td class="px-5 py-3.5 text-slate-500 max-w-xs truncate">{{ $item->description ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick='editItem(@json($item))' class="px-3 py-1.5 rounded-lg bg-blue-500/15 text-blue-400 text-xs hover:bg-blue-500/25 transition-all">✏️ Sửa</button>
                            <button onclick="deleteItem({{ $item->id }}, '{{ addslashes($item->name) }}')" class="px-3 py-1.5 rounded-lg bg-red-500/15 text-red-400 text-xs hover:bg-red-500/25 transition-all">🗑️ Xóa</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500"><div class="text-4xl mb-2">🥘</div><div>Chưa có món ăn nào</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div id="item-modal" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="if(event.target===this)closeModal()">
    <div class="card-glass rounded-3xl w-full max-w-lg mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white" id="modal-title">Thêm món ăn</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <form id="item-form" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Tên món *</label>
                    <input type="text" id="f-name" required maxlength="50" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none" placeholder="Tên món ăn...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Danh mục *</label>
                    <select id="f-category" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                        <option value="">— Chọn danh mục —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Đơn vị *</label>
                    <input type="text" id="f-unit" required maxlength="50" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none" placeholder="VD: Phần, Ly, Chai">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Giá (VNĐ) *</label>
                    <input type="number" id="f-price" required min="0" step="500" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none" placeholder="0">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Mô tả</label>
                    <textarea id="f-description" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none resize-none" placeholder="Mô tả ngắn về món ăn..."></textarea>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-slate-950 font-bold text-sm">Lưu món ăn</button>
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
        document.getElementById('modal-title').textContent = 'Thêm món ăn';
        ['f-name','f-unit','f-price','f-description'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('f-category').value = '';
        document.getElementById('item-modal').classList.remove('hidden');
        document.getElementById('item-modal').classList.add('flex');
    }

    function editItem(item) {
        editingId = item.id;
        document.getElementById('modal-title').textContent = 'Sửa món ăn';
        document.getElementById('f-name').value = item.name;
        document.getElementById('f-category').value = item.category_id;
        document.getElementById('f-price').value = item.price;
        document.getElementById('f-unit').value = item.unit;
        document.getElementById('f-description').value = item.description || '';
        document.getElementById('item-modal').classList.remove('hidden');
        document.getElementById('item-modal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('item-modal').classList.add('hidden');
        document.getElementById('item-modal').classList.remove('flex');
    }

    document.getElementById('item-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = {
            name: document.getElementById('f-name').value,
            category_id: document.getElementById('f-category').value,
            price: document.getElementById('f-price').value,
            unit: document.getElementById('f-unit').value,
            description: document.getElementById('f-description').value,
        };
        const url    = editingId ? `/items/${editingId}` : '/items';
        const method = editingId ? 'PUT' : 'POST';
        const res = await fetch(url, { method, headers, body: JSON.stringify(body) });
        if (res.ok) { closeModal(); location.reload(); }
        else { const d = await res.json(); alert(Object.values(d.errors||{message:'Lỗi!'}).flat().join('\n')); }
    });

    async function deleteItem(id, name) {
        if (!confirm(`Xóa món "${name}"?`)) return;
        const res = await fetch(`/items/${id}`, { method: 'DELETE', headers });
        if (res.ok) location.reload();
        else alert('Không thể xóa!');
    }

    // Search & filter
    function applyFilter() {
        const q   = document.getElementById('search-input').value.toLowerCase();
        const cat = document.getElementById('filter-cat').value;
        document.querySelectorAll('.item-row').forEach(r => {
            const nameMatch = r.dataset.name.includes(q);
            const catMatch  = !cat || r.dataset.cat === cat;
            r.style.display = (nameMatch && catMatch) ? '' : 'none';
        });
    }
    document.getElementById('search-input').addEventListener('input', applyFilter);
    document.getElementById('filter-cat').addEventListener('change', applyFilter);
</script>
@endpush
