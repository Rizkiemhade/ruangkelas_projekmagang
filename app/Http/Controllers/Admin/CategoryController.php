<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('courses')->orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Perubahan kategori berhasil disimpan.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->courses()->exists()) {
            return back()->withErrors(['category' => 'Kategori yang masih memiliki kelas tidak dapat dihapus.']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
