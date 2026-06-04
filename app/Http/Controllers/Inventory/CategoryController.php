<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['parent', 'children'])
            ->withCount('products')
            ->orderBy('name')
            ->paginate(50);

        return view('inventory.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::orderBy('name')->get();

        return view('inventory.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'parent_id'   => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $category = Category::create($request->only('name', 'parent_id', 'description'));

        ActivityLogger::log('category_create', "Category '{$category->name}' created", Category::class, $category->id);

        return redirect()->route('inventory.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        // Exclude self and own children to avoid circular nesting
        $parents = Category::where('id', '!=', $category->id)
            ->where(function ($q) use ($category) {
                $q->whereNull('parent_id')
                  ->orWhere('parent_id', '!=', $category->id);
            })
            ->orderBy('name')
            ->get();

        return view('inventory.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'parent_id'   => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string|max:1000',
        ]);

        // Prevent setting parent to a child (circular reference)
        if ($request->parent_id) {
            $childIds = $this->getAllChildIds($category);
            if (in_array($request->parent_id, $childIds)) {
                return back()->withErrors(['parent_id' => 'Cannot set a child category as parent.'])->withInput();
            }
        }

        $category->update($request->only('name', 'parent_id', 'description'));

        ActivityLogger::log('category_update', "Category '{$category->name}' updated", Category::class, $category->id);

        return redirect()->route('inventory.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete category with associated products.']);
        }

        if ($category->children()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete category that has sub-categories.']);
        }

        ActivityLogger::log('category_delete', "Category '{$category->name}' deleted", Category::class, $category->id);

        $category->delete();

        return redirect()->route('inventory.categories.index')->with('success', 'Category deleted successfully.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getAllChildIds(Category $category): array
    {
        $ids = [];
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids   = array_merge($ids, $this->getAllChildIds($child));
        }

        return $ids;
    }
}
