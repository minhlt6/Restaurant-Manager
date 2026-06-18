<?php

namespace App\Http\Controllers;

use App\Models\CustomerGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerGroupController extends Controller
{
    public function index(): JsonResponse|View
    {
        $customerGroups = CustomerGroup::withCount('customers')->orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json($customerGroups);
        }

        return view('customer-groups.index', compact('customerGroups'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $customerGroup = CustomerGroup::create($validated);

        return $request->expectsJson()
            ? response()->json($customerGroup, 201)
            : back()->with('success', 'Đã thêm nhóm khách "' . $customerGroup->name . '"!');
    }

    public function update(Request $request, CustomerGroup $customerGroup): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $customerGroup->update($validated);

        return $request->expectsJson()
            ? response()->json($customerGroup)
            : back()->with('success', 'Cập nhật nhóm khách thành công!');
    }

    public function destroy(Request $request, CustomerGroup $customerGroup): JsonResponse|RedirectResponse
    {
        // Check if group has customers
        if ($customerGroup->customers()->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nhóm đang có khách hàng, không thể xóa!'], 422);
            }
            return back()->with('error', 'Nhóm đang có khách hàng, không thể xóa!');
        }

        $name = $customerGroup->name;
        $customerGroup->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Deleted'])
            : back()->with('success', 'Đã xóa nhóm "' . $name . '"!');
    }

    public function show(CustomerGroup $customerGroup): JsonResponse
    {
        return response()->json($customerGroup->load('customers'));
    }
}
