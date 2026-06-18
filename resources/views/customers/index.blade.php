@extends('layouts.app')

@section('title', 'Khách hàng')
@section('header', 'Quản lý Khách hàng')
@section('breadcrumb', 'Trang chủ / Khách hàng')

@section('header-actions')
<button onclick="openModal()" class="btn-primary px-4 py-2 rounded-xl text-slate-950 font-semibold text-sm">
    + Thêm khách hàng
</button>
@endsection

@section('content')
<div class="mt-4 space-y-4">
    {{-- Search --}}
    <div class="card-glass rounded-2xl p-4 flex items-center gap-3">
        <span class="text-slate-400">🔍</span>
        <input type="text" id="search-input" placeholder="Tìm theo tên, SĐT, email..." class="flex-1 bg-transparent text-slate-200 text-sm placeholder-slate-500 focus:outline-none">
    </div>

    {{-- Table --}}
    <div class="card-glass rounded-2xl overflow-hidden">
        <table class="w-full text-sm" id="customers-table">
            <thead>
                <tr class="border-b border-slate-800">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tên</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Giới tính</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nhóm khách</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">SĐT</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Địa chỉ</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800" id="customers-body">
                @forelse($customers as $customer)
                <tr class="hover:bg-slate-800/30 transition-colors customer-row">
                    <td class="px-5 py-3.5 font-medium text-white customer-name">{{ $customer->name }}</td>
                    <td class="px-5 py-3.5 text-slate-400">{{ $customer->gender }}</td>
                    <td class="px-5 py-3.5">
                        @if($customer->customerGroup)
                        <span class="px-2.5 py-1 rounded-lg text-xs
                            {{ str_contains($customer->customerGroup->name, 'VIP') ? 'bg-purple-500/15 text-purple-400' : (str_contains($customer->customerGroup->name, 'sỉ') ? 'bg-amber-500/15 text-amber-400' : 'bg-slate-700 text-slate-300') }}">
                            {{ $customer->customerGroup->name }}
                        </span>
                        @else
                        <span class="text-slate-500 text-xs">Chưa phân loại</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-slate-300 customer-phone">{{ $customer->phone ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-slate-400 customer-email">{{ $customer->email ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-slate-400">{{ $customer->address ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick='editCustomer(@json($customer))' class="px-3 py-1.5 rounded-lg bg-blue-500/15 text-blue-400 text-xs hover:bg-blue-500/25 transition-all">✏️ Sửa</button>
                            @if(session('employee.role') == 1)
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Xóa khách hàng này?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 rounded-lg bg-red-500/15 text-red-400 text-xs hover:bg-red-500/25 transition-all">🗑️ Xóa</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                        <div class="text-4xl mb-2">👥</div>
                        <div>Chưa có khách hàng nào</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div id="customer-modal" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="if(event.target===this)closeModal()">
    <div class="card-glass rounded-3xl w-full max-w-lg mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white" id="modal-title">Thêm khách hàng</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>

        <form id="customer-form" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <input type="hidden" name="id" id="customer-id">

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Tên khách hàng *</label>
                    <input type="text" name="name" id="f-name" required maxlength="30" placeholder="Họ và tên..."
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Giới tính *</label>
                    <select name="gender" id="f-gender" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Nhóm khách</label>
                    <select name="customer_group_id" id="f-group" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                        <option value="">— Chưa phân loại —</option>
                        @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Số điện thoại</label>
                    <input type="text" name="phone" id="f-phone" maxlength="11" placeholder="0901234567"
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Email</label>
                    <input type="email" name="email" id="f-email" maxlength="50" placeholder="email@example.com"
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Địa chỉ</label>
                    <input type="text" name="address" id="f-address" maxlength="250" placeholder="Địa chỉ..."
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                </div>
            </div>

            @if($errors->any())
            <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs">
                @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
            </div>
            @endif

            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-slate-950 font-bold text-sm">
                Lưu khách hàng
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModal() {
        document.getElementById('modal-title').textContent = 'Thêm khách hàng';
        document.getElementById('customer-form').action = '{{ route("customers.store") }}';
        document.getElementById('form-method').value = 'POST';
        ['name','phone','email','address'].forEach(f => document.getElementById('f-'+f).value = '');
        document.getElementById('f-gender').value = 'Nam';
        document.getElementById('f-group').value = '';
        document.getElementById('customer-modal').classList.remove('hidden');
        document.getElementById('customer-modal').classList.add('flex');
    }

    function editCustomer(c) {
        document.getElementById('modal-title').textContent = 'Sửa khách hàng';
        document.getElementById('customer-form').action = `/customers/${c.id}`;
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('f-name').value = c.name || '';
        document.getElementById('f-gender').value = c.gender || 'Nam';
        document.getElementById('f-group').value = c.customer_group_id || '';
        document.getElementById('f-phone').value = c.phone || '';
        document.getElementById('f-email').value = c.email || '';
        document.getElementById('f-address').value = c.address || '';
        document.getElementById('customer-modal').classList.remove('hidden');
        document.getElementById('customer-modal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('customer-modal').classList.add('hidden');
        document.getElementById('customer-modal').classList.remove('flex');
    }

    // Live search
    document.getElementById('search-input').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.customer-row').forEach(row => {
            const name  = row.querySelector('.customer-name')?.textContent.toLowerCase() || '';
            const phone = row.querySelector('.customer-phone')?.textContent.toLowerCase() || '';
            const email = row.querySelector('.customer-email')?.textContent.toLowerCase() || '';
            row.style.display = (name+phone+email).includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
