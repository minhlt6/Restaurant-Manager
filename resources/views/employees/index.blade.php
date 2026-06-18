@extends('layouts.app')

@section('title', 'Nhân viên')
@section('header', 'Quản lý Nhân viên')
@section('breadcrumb', 'Trang chủ / Nhân viên')

@section('header-actions')
<button onclick="openModal()" class="btn-primary px-4 py-2 rounded-xl text-slate-950 font-semibold text-sm">
    + Thêm nhân viên
</button>
@endsection

@section('content')
<div class="mt-4 card-glass rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800">
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nhân viên</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tài khoản</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Vai trò</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Ngày sinh</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Địa chỉ</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Hành động</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($employees as $emp)
            <tr class="hover:bg-slate-800/30 transition-colors">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-slate-950 font-bold text-sm shrink-0">
                            {{ mb_substr($emp->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-medium text-white">{{ $emp->name }}</div>
                            <div class="text-xs text-slate-500">{{ $emp->gender }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    <span class="font-mono text-slate-300">{{ $emp->username }}</span>
                </td>
                <td class="px-5 py-3.5">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $emp->role === 1 ? 'bg-amber-500/15 text-amber-400' : 'bg-slate-700 text-slate-300' }}">
                        {{ $emp->role === 1 ? '👑 Quản lý' : '👤 Nhân viên' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-slate-400">{{ $emp->birthday ? $emp->birthday->format('d/m/Y') : '—' }}</td>
                <td class="px-5 py-3.5 text-slate-400">{{ $emp->address ?? '—' }}</td>
                <td class="px-5 py-3.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick='editEmployee(@json($emp->makeVisible("password")->setHidden(["password"])))' class="px-3 py-1.5 rounded-lg bg-blue-500/15 text-blue-400 text-xs hover:bg-blue-500/25 transition-all">✏️ Sửa</button>
                        @if($emp->id !== session('employee.id'))
                        <form method="POST" action="{{ route('employees.destroy', $emp) }}" onsubmit="return confirm('Xóa nhân viên này?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 rounded-lg bg-red-500/15 text-red-400 text-xs hover:bg-red-500/25 transition-all">🗑️ Xóa</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500"><div class="text-4xl mb-2">👤</div><div>Chưa có nhân viên nào</div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div id="emp-modal" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay" onclick="if(event.target===this)closeModal()">
    <div class="card-glass rounded-3xl w-full max-w-lg mx-4 p-6 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white" id="modal-title">Thêm nhân viên</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>
        <form id="emp-form" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Họ và tên *</label>
                    <input type="text" name="name" id="f-name" required maxlength="30" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none" placeholder="Nguyễn Văn A">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Giới tính *</label>
                    <select name="gender" id="f-gender" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                        <option value="Nam">Nam</option><option value="Nữ">Nữ</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Ngày sinh *</label>
                    <input type="date" name="birthday" id="f-birthday" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Tên đăng nhập *</label>
                    <input type="text" name="username" id="f-username" required maxlength="50" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none" placeholder="username">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Mật khẩu <span id="pwd-hint" class="text-slate-600">(để trống = không đổi)</span></label>
                    <input type="password" name="password" id="f-password" minlength="6" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none" placeholder="Tối thiểu 6 ký tự">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Vai trò *</label>
                    <select name="role" id="f-role" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none">
                        <option value="0">Nhân viên</option><option value="1">Quản lý</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Địa chỉ</label>
                    <input type="text" name="address" id="f-address" maxlength="250" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:border-amber-400/50 focus:outline-none" placeholder="Địa chỉ...">
                </div>
            </div>
            @if($errors->any())
            <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs">
                @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
            </div>
            @endif
            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-slate-950 font-bold text-sm">Lưu nhân viên</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModal() {
        document.getElementById('modal-title').textContent = 'Thêm nhân viên';
        document.getElementById('emp-form').action = '{{ route("employees.store") }}';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('pwd-hint').textContent = '(bắt buộc khi thêm mới)';
        document.getElementById('f-password').required = true;
        ['name','username','password','address'].forEach(f => document.getElementById('f-'+f).value = '');
        document.getElementById('f-gender').value = 'Nam';
        document.getElementById('f-role').value = '0';
        document.getElementById('emp-modal').classList.remove('hidden');
        document.getElementById('emp-modal').classList.add('flex');
    }

    function editEmployee(e) {
        document.getElementById('modal-title').textContent = 'Sửa nhân viên';
        document.getElementById('emp-form').action = `/employees/${e.id}`;
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('pwd-hint').textContent = '(để trống = không đổi)';
        document.getElementById('f-password').required = false;
        document.getElementById('f-name').value = e.name;
        document.getElementById('f-gender').value = e.gender;
        document.getElementById('f-birthday').value = e.birthday?.slice(0,10) || '';
        document.getElementById('f-username').value = e.username;
        document.getElementById('f-password').value = '';
        document.getElementById('f-role').value = e.role;
        document.getElementById('f-address').value = e.address || '';
        document.getElementById('emp-modal').classList.remove('hidden');
        document.getElementById('emp-modal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('emp-modal').classList.add('hidden');
        document.getElementById('emp-modal').classList.remove('flex');
    }
</script>
@endpush
