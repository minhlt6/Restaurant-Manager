<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): JsonResponse|View
    {
        $employees = Employee::orderBy('role', 'desc')->orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json($employees);
        }

        return view('employees.index', compact('employees'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:30'],
            'gender'   => ['required', 'string', 'max:5'],
            'address'  => ['nullable', 'string', 'max:250'],
            'birthday' => ['required', 'date'],
            'username' => ['required', 'string', 'max:50', 'unique:employees,username'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', 'integer', 'in:0,1'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $employee = Employee::create($validated);

        return $request->expectsJson()
            ? response()->json($employee, 201)
            : back()->with('success', 'Đã thêm nhân viên "' . $employee->name . '"!');
    }

    public function update(Request $request, Employee $employee): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:30'],
            'gender'   => ['required', 'string', 'max:5'],
            'address'  => ['nullable', 'string', 'max:250'],
            'birthday' => ['required', 'date'],
            'username' => ['required', 'string', 'max:50', 'unique:employees,username,' . $employee->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role'     => ['required', 'integer', 'in:0,1'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $employee->update($validated);

        return $request->expectsJson()
            ? response()->json($employee)
            : back()->with('success', 'Cập nhật thông tin nhân viên thành công!');
    }

    public function destroy(Request $request, Employee $employee): JsonResponse|RedirectResponse
    {
        // Prevent self-deletion
        if ($employee->id === (int) session('employee.id')) {
            if ($request->expectsJson()) return response()->json(['message' => 'Không thể xóa chính mình!'], 422);
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập!');
        }

        $name = $employee->name;
        $employee->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Deleted'])
            : back()->with('success', 'Đã xóa nhân viên "' . $name . '"!');
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json($employee);
    }

    public function create(): never { abort(404); }
    public function edit(Employee $employee): never { abort(404); }
}
