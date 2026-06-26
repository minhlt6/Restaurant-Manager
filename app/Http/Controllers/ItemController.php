<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(): JsonResponse|View
    {
        $items      = Item::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json($items);
        }

        return view('items.index', compact('items', 'categories'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'unit'        => ['required', 'string', 'max:255'],
        ]);

        $item = Item::create($validated);

        return $request->expectsJson()
            ? response()->json($item->load('category'), 201)
            : back()->with('success', 'Đã thêm món "' . $item->name . '"!');
    }

    public function update(Request $request, Item $item): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'unit'        => ['required', 'string', 'max:255'],
        ]);

        $item->update($validated);

        return $request->expectsJson()
            ? response()->json($item->load('category'))
            : back()->with('success', 'Cập nhật món ăn thành công!');
    }

    public function destroy(Request $request, Item $item): JsonResponse|RedirectResponse
    {
        $name = $item->name;
        $item->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Deleted'])
            : back()->with('success', 'Đã xóa món "' . $name . '"!');
    }

    public function show(Item $item): JsonResponse
    {
        return response()->json($item->load('category'));
    }

    public function create(): never { abort(404); }
    public function edit(Item $item): never { abort(404); }
}
