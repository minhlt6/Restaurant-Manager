<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): JsonResponse|View
    {
        $categories = Category::withCount('items')->orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json($categories);
        }

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:50']]);
        $category  = Category::create($validated);

        return $request->expectsJson()
            ? response()->json($category, 201)
            : back()->with('success', 'Đã thêm danh mục "' . $category->name . '"!');
    }

    public function update(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:50']]);
        $category->update($validated);

        return $request->expectsJson()
            ? response()->json($category)
            : back()->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroy(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Deleted'])
            : back()->with('success', 'Đã xóa danh mục "' . $name . '"!');
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load('items'));
    }

    public function create(): never { abort(404); }
    public function edit(Category $category): never { abort(404); }
}
