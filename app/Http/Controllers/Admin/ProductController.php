<?php
// app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category','images'])
            ->withCount('orderItems')
            ->whereNull('deleted_at');

        if ($request->filled('q')) {
            $query->where('name','like','%'.$request->q.'%');
        }
        if ($request->filled('category')) {
            $query->where('category_id',$request->category);
        }
        if ($request->filled('status')) {
            $query->where('status',$request->status);
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::active()->get();

        return view('admin.products.index', compact('products','categories'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required','string','max:200'],
            'category_id'  => ['required','exists:categories,id'],
            'description'  => ['required','string'],
            'details'      => ['nullable','string'],
            'price'        => ['required','numeric','min:0'],
            'stock'        => ['required','integer','min:0'],
            'weight'       => ['required','integer','min:0'],
            'status'       => ['required','in:available,sold_out,preorder,coming_soon'],
            'is_limited'   => ['boolean'],
            'release_date' => ['nullable','date'],
            'sizes'        => ['nullable','array'],
            'sizes.*'      => ['string','in:S,M,L,XL,XXL,XXXL,FREE SIZE'],
            'images'       => ['nullable','array','max:6'],
            'images.*'     => ['image','mimes:jpg,jpeg,png,webp','max:3072'],
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create([
                'name'         => $validated['name'],
                'category_id'  => $validated['category_id'],
                'description'  => $validated['description'],
                'details'      => $validated['details'] ?? null,
                'price'        => $validated['price'],
                'stock'        => $validated['stock'],
                'weight'       => $validated['weight'],
                'status'       => $validated['status'],
                'is_limited'   => $request->boolean('is_limited'),
                'release_date' => $validated['release_date'] ?? null,
                'sizes'        => $validated['sizes'] ?? null,
            ]);

            // Upload images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $i => $file) {
                    $path = $file->store('products','public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => ($i === 0),
                        'sort_order' => $i,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')
                ->with('success','Produk berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error','Gagal menyimpan produk: '.$e->getMessage());
        }
    }

    public function show(Product $product)
    {
        $product->load(['category','images','reviews.user','orderItems']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load('images');
        $categories = Category::active()->get();
        return view('admin.products.edit', compact('product','categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => ['required','string','max:200'],
            'category_id'    => ['required','exists:categories,id'],
            'description'    => ['required','string'],
            'details'        => ['nullable','string'],
            'price'          => ['required','numeric','min:0'],
            'stock'          => ['required','integer','min:0'],
            'weight'         => ['required','integer','min:0'],
            'status'         => ['required','in:available,sold_out,preorder,coming_soon'],
            'is_limited'     => ['boolean'],
            'release_date'   => ['nullable','date'],
            'sizes'          => ['nullable','array'],
            'sizes.*'        => ['string','in:S,M,L,XL,XXL,XXXL,FREE SIZE'],
            'new_images'     => ['nullable','array','max:6'],
            'new_images.*'   => ['image','mimes:jpg,jpeg,png,webp','max:3072'],
            'deleted_images' => ['nullable','array'], // BARU: untuk hapus gambar
            'deleted_images.*' => ['exists:product_images,id'],
            'primary_image_id' => ['nullable','exists:product_images,id'],
        ]);

        DB::beginTransaction();
        try {
            $product->update([
                'name'         => $validated['name'],
                'category_id'  => $validated['category_id'],
                'description'  => $validated['description'],
                'details'      => $validated['details'] ?? null,
                'price'        => $validated['price'],
                'stock'        => $validated['stock'],
                'weight'       => $validated['weight'],
                'status'       => $validated['status'],
                'is_limited'   => $request->boolean('is_limited'),
                'release_date' => $validated['release_date'] ?? null,
                'sizes'        => $validated['sizes'] ?? null,
            ]);

            // ──────────────────────────────────────────────────────────
            // HAPUS GAMBAR YANG DIPILIH
            // ──────────────────────────────────────────────────────────
            if ($request->has('deleted_images')) {
                foreach ($request->deleted_images as $imageId) {
                    $image = ProductImage::find($imageId);
                    if ($image && $image->product_id === $product->id) {
                        // Hapus file fisik
                        if (Storage::disk('public')->exists($image->image_path)) {
                            Storage::disk('public')->delete($image->image_path);
                        }
                        // Hapus record
                        $image->delete();
                    }
                }
            }

            // ──────────────────────────────────────────────────────────
            // UPLOAD GAMBAR BARU
            // ──────────────────────────────────────────────────────────
            if ($request->hasFile('new_images')) {
                $nextOrder = $product->images()->max('sort_order') + 1;
                foreach ($request->file('new_images') as $i => $file) {
                    $path = $file->store('products','public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => false,
                        'sort_order' => $nextOrder + $i,
                    ]);
                }
            }

            // ──────────────────────────────────────────────────────────
            // SET PRIMARY IMAGE
            // ──────────────────────────────────────────────────────────
            if ($request->filled('primary_image_id')) {
                // Reset semua primary flag
                ProductImage::where('product_id', $product->id)
                    ->update(['is_primary' => false]);
                // Set primary image baru
                ProductImage::where('id', $request->primary_image_id)
                    ->where('product_id', $product->id)
                    ->update(['is_primary' => true]);
            } else {
                // Jika tidak ada primary image yang dipilih, cek apakah masih ada primary
                $hasPrimary = ProductImage::where('product_id', $product->id)
                    ->where('is_primary', true)
                    ->exists();
                
                // Jika tidak ada primary dan masih ada gambar, jadikan gambar pertama sebagai primary
                if (!$hasPrimary) {
                    $firstImage = ProductImage::where('product_id', $product->id)
                        ->orderBy('sort_order')
                        ->first();
                    if ($firstImage) {
                        $firstImage->update(['is_primary' => true]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')
                ->with('success','Produk berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error','Gagal memperbarui produk: '.$e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        // Hapus semua gambar dari storage
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }
        
        $product->delete();
        return back()->with('success','Produk berhasil dihapus (soft delete).');
    }

    // AJAX — hapus satu gambar produk
    public function deleteImage(ProductImage $image)
    {
        // Hapus file fisik
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $productId  = $image->product_id;
        $wasPrimary = $image->is_primary;
        $image->delete();

        // Jika gambar yang dihapus adalah primary, jadikan gambar pertama sebagai primary
        if ($wasPrimary) {
            $first = ProductImage::where('product_id', $productId)
                ->orderBy('sort_order')
                ->first();
            if ($first) {
                $first->update(['is_primary' => true]);
            }
        }

        return response()->json(['success' => true]);
    }

    // AJAX — set gambar primary
    public function setPrimaryImage(ProductImage $image)
    {
        ProductImage::where('product_id', $image->product_id)
            ->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return response()->json(['success' => true]);
    }
}