<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $employee = Employee::where('username', $credentials['username'])->first();

        if (! $employee || ! Hash::check($credentials['password'], $employee->password)) {
            return back()->withErrors([
                'username' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();
        session([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'role' => (int) $employee->role,
                'username' => $employee->username,
            ],
        ]);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('employee');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}