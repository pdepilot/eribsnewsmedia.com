<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('categories.manage'), 403);

        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('articles')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasPermission('categories.manage'), 403);

        return view('admin.categories.form', ['category' => new Category(['sort_order' => 0])]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Category::query()->create([
            'name' => $data['name'],
            'slug' => Category::uniqueSlug($data['slug'] ?: $data['name']),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'show_on_home' => $request->boolean('show_on_home'),
            'show_in_nav' => $request->boolean('show_in_nav'),
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        abort_unless(auth()->user()->hasPermission('categories.manage'), 403);

        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $category->update([
            'name' => $data['name'],
            'slug' => Category::uniqueSlug($data['slug'] ?: $data['name'], $category->id),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'show_on_home' => $request->boolean('show_on_home'),
            'show_in_nav' => $request->boolean('show_in_nav'),
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('categories.manage'), 403);

        if ($category->articles()->exists()) {
            return back()->withErrors(['category' => 'Move the articles in this category before deleting it.']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }
}
