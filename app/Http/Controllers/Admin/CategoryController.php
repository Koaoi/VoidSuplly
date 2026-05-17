<?php
// app/Http/Controllers/Admin/CategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:100','unique:categories,name'],
            'description' => ['nullable','string','max:500'],
            'image'       => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'is_active'   => ['boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories','public');
        }

        Category::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image'       => $imagePath,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success','Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:100','unique:categories,name,'.$category->id],
            'description' => ['nullable','string','max:500'],
            'image'       => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'is_active'   => ['boolean'],
        ]);

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $imagePath = $request->file('image')->store('categories','public');
        }

        $category->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image'       => $imagePath,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success','Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error','Kategori tidak bisa dihapus karena masih memiliki produk.');
        }
        if ($category->image) Storage::disk('public')->delete($category->image);
        $category->delete();
        return back()->with('success','Kategori berhasil dihapus.');
    }
}