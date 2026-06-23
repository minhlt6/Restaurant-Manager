<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): JsonResponse|View
    {
        $customers = Customer::with('customerGroup')->orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json($customers);
        }

        $groups = CustomerGroup::orderBy('name')->get();
        return view('customers.index', compact('customers', 'groups'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'name'    => ['required', 'string', 'max:30'],
            'gender'  => ['required', 'string', 'max:5'],
            'address' => ['nullable', 'string', 'max:250'],
            'phone'   => ['nullable', 'string', 'max:11', 'unique:customers,phone'],
            'email'   => ['nullable', 'email', 'max:50', 'unique:customers,email'],
        ]);

        $customer = Customer::create($validated);

        return $request->expectsJson()
            ? response()->json($customer, 201)
            : back()->with('success', 'Đã thêm khách hàng "' . $customer->name . '"!');
    }

    public function update(Request $request, Customer $customer): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'name'    => ['required', 'string', 'max:30'],
            'gender'  => ['required', 'string', 'max:5'],
            'address' => ['nullable', 'string', 'max:250'],
            'phone'   => ['nullable', 'string', 'max:11', 'unique:customers,phone,' . $customer->id],
            'email'   => ['nullable', 'email', 'max:50', 'unique:customers,email,' . $customer->id],
        ]);

        $customer->update($validated);

        return $request->expectsJson()
            ? response()->json($customer)
            : back()->with('success', 'Cập nhật thông tin khách hàng thành công!');
    }

    public function destroy(Request $request, Customer $customer): JsonResponse|RedirectResponse
    {
        if ($customer->orders()->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Khách hàng đã có hóa đơn, không thể xóa!'], 422);
            }
            return back()->with('error', 'Khách hàng đã có hóa đơn, không thể xóa!');
        }

        $name = $customer->name;
        $customer->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Deleted'])
            : back()->with('success', 'Đã xóa khách hàng "' . $name . '"!');
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer->load('customerGroup'));
    }

    public function create(): never { abort(404); }
    public function edit(Customer $customer): never { abort(404); }
}
