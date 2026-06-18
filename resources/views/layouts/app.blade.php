<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nhà hàng') — Quản lý Nhà hàng</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all .18s ease; }
        .sidebar-link:hover { background: rgba(251,191,36,.08); color: #fbbf24; }
        .sidebar-link.active { background: rgba(251,191,36,.15); color: #fbbf24; border-right: 3px solid #fbbf24; }
        .card-glass { background: rgba(15,23,42,.7); backdrop-filter: blur(12px); border: 1px solid rgba(51,65,85,.6); }
        .btn-primary { background: linear-gradient(135deg,#f59e0b,#d97706); transition: all .18s; }
        .btn-primary:hover { background: linear-gradient(135deg,#fbbf24,#f59e0b); transform: translateY(-1px); box-shadow: 0 4px 15px rgba(245,158,11,.3); }
        .btn-danger { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.3); color: #f87171; transition: all .18s; }
        .btn-danger:hover { background: rgba(239,68,68,.25); }
        .modal-overlay { background: rgba(0,0,0,.7); backdrop-filter: blur(4px); }
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        .fade-in { animation: fadeIn .25s ease; }
        @keyframes fadeIn { from { opacity:0; transform: translateY(6px); } to { opacity:1; transform: translateY(0); } }
        .table-status-free     { background: rgba(34,197,94,.12); color: #4ade80; border: 1px solid rgba(34,197,94,.25); }
        .table-status-serving  { background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.25); }
        .table-status-reserved { background: rgba(245,158,11,.12); color: #fbbf24; border: 1px solid rgba(245,158,11,.25); }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex" style="font-family:'Inter',sans-serif">

    {{-- ===== SIDEBAR ===== --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 border-r border-slate-800 flex flex-col transition-transform duration-300">
        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-400 flex items-center justify-center text-slate-950 font-black text-lg">🍜</div>
                <div>
                    <div class="font-bold text-white text-sm leading-tight">Nhà Hàng Ánh Sáng</div>
                    <div class="text-xs text-slate-500">Restaurant Management</div>
                </div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-3">
            {{-- Nhân viên & Quản lý --}}
            <div class="px-3 py-1.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nghiệp vụ</div>

            <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="text-base">📊</span> Dashboard
            </a>
            <a href="{{ route('dining-tables.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('dining-tables.*') && !request()->routeIs('dining-tables.manage') ? 'active' : '' }}">
                <span class="text-base">🍽️</span> Sơ đồ bàn
            </a>
            <a href="{{ route('customers.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <span class="text-base">👥</span> Khách hàng
            </a>
            <a href="{{ route('orders.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <span class="text-base">🧾</span> Hóa đơn
            </a>

            {{-- Manager only --}}
            @if(session('employee.role') == 1)
            <div class="px-3 py-1.5 mt-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Quản lý</div>
            <a href="{{ route('employees.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <span class="text-base">👤</span> Nhân viên
            </a>
            <a href="{{ route('categories.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <span class="text-base">📂</span> Danh mục món
            </a>
            <a href="{{ route('items.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('items.*') ? 'active' : '' }}">
                <span class="text-base">🥘</span> Thực đơn
            </a>
            <a href="{{ route('customer-groups.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('customer-groups.*') ? 'active' : '' }}">
                <span class="text-base">🏷️</span> Nhóm khách
            </a>
            <a href="{{ route('dining-tables.manage') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('dining-tables.manage') ? 'active' : '' }}">
                <span class="text-base">🪑</span> Quản lý bàn
            </a>
            <a href="{{ route('reports.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-300 {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <span class="text-base">📈</span> Báo cáo
            </a>
            @endif
        </nav>

        {{-- User info --}}
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-slate-950 font-bold text-sm">
                    {{ mb_substr(session('employee.name', 'U'), 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold truncate">{{ session('employee.name', 'Nhân viên') }}</div>
                    <div class="text-xs text-slate-500">{{ session('employee.role') == 1 ? 'Quản lý' : 'Nhân viên' }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-sm text-slate-400 border border-slate-700 hover:border-red-500/50 hover:text-red-400 transition-all">
                    <span>🚪</span> Đăng xuất
                </button>
            </form>
        </div>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 flex flex-col ml-64">
        {{-- Topbar --}}
        <header class="sticky top-0 z-30 bg-slate-950/80 backdrop-blur border-b border-slate-800 px-6 py-3 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-white">@yield('header', 'Dashboard')</h1>
                <div class="text-xs text-slate-500">@yield('breadcrumb', 'Trang chủ')</div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs text-slate-500 bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-800">
                    {{ now()->format('d/m/Y H:i') }}
                </div>
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-6 pt-4">
            @if(session('success'))
            <div class="mb-4 flex items-center gap-3 bg-green-500/10 border border-green-500/25 text-green-400 px-4 py-3 rounded-xl fade-in" id="flash-success">
                <span>✅</span> {{ session('success') }}
                <button onclick="document.getElementById('flash-success').remove()" class="ml-auto text-green-300 hover:text-white">✕</button>
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 flex items-center gap-3 bg-red-500/10 border border-red-500/25 text-red-400 px-4 py-3 rounded-xl fade-in" id="flash-error">
                <span>❌</span> {{ session('error') }}
                <button onclick="document.getElementById('flash-error').remove()" class="ml-auto text-red-300 hover:text-white">✕</button>
            </div>
            @endif
        </div>

        {{-- Page content --}}
        <main class="flex-1 px-6 pb-8">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
