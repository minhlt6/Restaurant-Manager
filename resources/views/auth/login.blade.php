<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — Quản lý Nhà hàng</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: radial-gradient(ellipse at top, #1e1b0e 0%, #0f172a 50%, #020617 100%); min-height: 100vh; }
        .login-card { background: rgba(15,23,42,.85); backdrop-filter: blur(20px); border: 1px solid rgba(251,191,36,.12); }
        .input-field {
            background: rgba(30,41,59,.6);
            border: 1px solid rgba(51,65,85,.7);
            color: #e2e8f0;
            transition: all .2s;
        }
        .input-field:focus { outline: none; border-color: rgba(251,191,36,.5); box-shadow: 0 0 0 3px rgba(251,191,36,.08); }
        .btn-login { background: linear-gradient(135deg, #f59e0b, #d97706); font-weight: 600; transition: all .2s; }
        .btn-login:hover { background: linear-gradient(135deg, #fbbf24, #f59e0b); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,158,11,.35); }
        .btn-login:active { transform: translateY(0); }
        .orb1 { position: fixed; top: -200px; right: -200px; width: 600px; height: 600px; background: radial-gradient(circle, rgba(245,158,11,.06) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
        .orb2 { position: fixed; bottom: -200px; left: -200px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(59,130,246,.04) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="orb1"></div>
    <div class="orb2"></div>

    <div class="w-full max-w-md px-4">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-amber-400 to-orange-500 text-4xl mb-4 shadow-2xl shadow-amber-500/20">
                🍜
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Nhà Hàng Ánh Sáng</h1>
            <p class="text-slate-400 mt-1 text-sm">Hệ thống quản lý nhà hàng</p>
        </div>

        {{-- Card --}}
        <div class="login-card rounded-3xl p-8 shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-6">Đăng nhập hệ thống</h2>

            @if ($errors->any())
            <div class="mb-5 flex items-start gap-3 bg-red-500/10 border border-red-500/25 text-red-400 px-4 py-3 rounded-2xl text-sm">
                <span class="text-base mt-0.5">⚠️</span>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2" for="username">
                        Tên đăng nhập
                    </label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        autocomplete="username"
                        value="{{ old('username') }}"
                        placeholder="Nhập tên đăng nhập..."
                        required
                        class="input-field w-full px-4 py-3 rounded-xl text-sm"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2" for="password">
                        Mật khẩu
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Nhập mật khẩu..."
                            required
                            class="input-field w-full px-4 py-3 rounded-xl text-sm pr-12"
                        >
                        <button type="button" onclick="togglePwd()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white text-lg" tabindex="-1">
                            <span id="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login w-full py-3 rounded-xl text-slate-950 text-sm mt-2">
                    Đăng nhập →
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-xs text-slate-500 text-center">
                Tài khoản demo: <span class="text-amber-400 font-mono">admin</span> / <span class="text-amber-400 font-mono">password</span>
            </div>
        </div>

        <p class="text-center text-xs text-slate-600 mt-6">© {{ date('Y') }} Nhà Hàng Ánh Sáng. All rights reserved.</p>
    </div>

    <script>
        function togglePwd() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eye-icon');
            if (input.type === 'password') { input.type = 'text'; icon.textContent = '🙈'; }
            else { input.type = 'password'; icon.textContent = '👁️'; }
        }
    </script>
</body>
</html>