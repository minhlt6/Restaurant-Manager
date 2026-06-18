@extends('layouts.app')

@section('title', 'Nhóm khách hàng')
@section('header', 'Nhóm khách hàng')
@section('breadcrumb', 'Trang chủ / Nhóm khách hàng')

@section('header-actions')
<button onclick="openModal()" class="btn-primary px-4 py-2 rounded-xl text-slate-950 font-semibold text-sm">+ Thêm nhóm</button>
@endsection

@section('content')
<div class="mt-4 grid md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($customerGroups as $group)
    <div class="card-glass rounded-2xl p-5 fade-in" id="group-card-{{ $group->id }}">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl
                {{ str_contains($group->name, 'VIP') ? 'bg-purple-500/15' : (str_contains($group->name, 'sỉ') ? 'bg-amber-500/15' : 'bg-slate-700') }}">
                {{ str_contains($group->name, 'VIP') ? '⭐' : (str_contains($group->name, 'sỉ') ? '🏢' : '👤') }}
            </div>
            <div class="flex gap-2">
                <button onclick='editGroup(@json($group))' class="px-2.5 py-1 rounded-lg bg-blue-500/15 text-blue-400 text-xs hover:bg-blue-500/25 transition-all">✏️</button>
                <button onclick="deleteGroup({{ $group->id }}, '{{ $group->name }}')" class="px-2.5 py-1 rounded-lg bg-red-500/15 text-red-400 text-xs hover:bg-red-500/25 transition-all">🗑️</button>
            </div>
        </div>
        <h3 class="font-bold text-white text-base">{{ $group->name }}</h3>
        <p class="text-sm text-slate-400 mt-1 leading-relaxed">{{ $group->description ?? 'Không có mô tả' }}</p>
        <div class="mt-3 pt-3 border-t border-slate-700 text-xs text-slate-500">
            {{ $group->customers_count ?? 0 }} khách hàng
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-12 text-slate-500">
        <div class="text-4xl mb-2">🏷️</div><div>Chưa có nhóm khách hàng nào</div>
    </div>
    @endforelse
</div>

{{-- Modal --}}
<div id="group-modal" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="if(event.target===this)closeModal()">
    <div class="card-glass rounded-3xl w-full max-w-md mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white" id="modal-title">Thêm nhóm khách</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <form id="group-form" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Tên nhóm *</label>
                <input type="text" id="f-name" required maxlength="255" placeholder="VD: Khách VIP, Khách sỉ..."
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Mô tả</label>
                <textarea id="f-description" rows="3" placeholder="Mô tả về nhóm khách hàng..."
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none resize-none"></textarea>
            </div>
            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-slate-950 font-bold text-sm">Lưu nhóm</button>
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
        document.getElementById('modal-title').textContent = 'Thêm nhóm khách';
        document.getElementById('f-name').value = '';
        document.getElementById('f-description').value = '';
        document.getElementById('group-modal').classList.remove('hidden');
        document.getElementById('group-modal').classList.add('flex');
    }

    function editGroup(g) {
        editingId = g.id;
        document.getElementById('modal-title').textContent = 'Sửa nhóm khách';
        document.getElementById('f-name').value = g.name;
        document.getElementById('f-description').value = g.description || '';
        document.getElementById('group-modal').classList.remove('hidden');
        document.getElementById('group-modal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('group-modal').classList.add('hidden');
        document.getElementById('group-modal').classList.remove('flex');
    }

    document.getElementById('group-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = { name: document.getElementById('f-name').value, description: document.getElementById('f-description').value };
        const url    = editingId ? `/customer-groups/${editingId}` : '/customer-groups';
        const method = editingId ? 'PUT' : 'POST';
        const res = await fetch(url, { method, headers, body: JSON.stringify(body) });
        if (res.ok) { closeModal(); location.reload(); }
        else { const d = await res.json(); alert(Object.values(d.errors||{message:'Lỗi'}).flat().join('\n')); }
    });

    async function deleteGroup(id, name) {
        if (!confirm(`Xóa nhóm "${name}"?`)) return;
        const res = await fetch(`/customer-groups/${id}`, { method: 'DELETE', headers });
        if (res.ok) { document.getElementById('group-card-'+id)?.remove(); }
        else alert('Không thể xóa! Nhóm đang có khách hàng.');
    }
</script>
@endpush
