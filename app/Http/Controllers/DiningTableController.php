<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DiningTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    public function index(): JsonResponse|View
    {
        $tables = DiningTable::with(['orders' => fn($q) => $q->where('status', 0)->with(['customer', 'employee'])])->orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json($tables);
        }

        $customers = Customer::with('customerGroup')->orderBy('name')->get();

        return view('dining-tables.index', [
            'tables'        => $tables,
            'freeTables'    => $tables->where('status', 0)->count(),
            'servingTables' => $tables->where('status', 1)->count(),
            'reservedTables'=> $tables->where('status', 2)->count(),
            'customers'     => $customers,
        ]);
    }

    public function manage(): View
    {
        $tables = DiningTable::orderBy('name')->get();
        return view('dining-tables.manage', compact('tables'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status'   => ['required', 'integer', 'in:0,1,2'],
        ]);

        $diningTable = DiningTable::create($validated);

        return $request->expectsJson()
            ? response()->json($diningTable, 201)
            : back()->with('success', 'Đã thêm bàn "' . $diningTable->name . '" thành công!');
    }

    public function update(Request $request, DiningTable $diningTable): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status'   => ['required', 'integer', 'in:0,1,2'],
        ]);

        $diningTable->update($validated);

        return $request->expectsJson()
            ? response()->json($diningTable)
            : back()->with('success', 'Đã cập nhật bàn thành công!');
    }

    public function destroy(Request $request, DiningTable $diningTable): JsonResponse|RedirectResponse
    {
        // Check if table has active orders
        if ($diningTable->orders()->where('status', 0)->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bàn đang có khách, không thể xóa!'], 422);
            }
            return back()->with('error', 'Bàn đang có khách, không thể xóa!');
        }

        $name = $diningTable->name;
        $diningTable->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Deleted'])
            : back()->with('success', 'Đã xóa bàn "' . $name . '"!');
    }

    public function show(DiningTable $diningTable): JsonResponse
    {
        return response()->json($diningTable);
    }

    public function create(): never { abort(404); }
    public function edit(DiningTable $diningTable): never { abort(404); }
}
