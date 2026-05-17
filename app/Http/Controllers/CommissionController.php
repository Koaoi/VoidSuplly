<?php
// app/Http/Controllers/CommissionController.php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Daftar commission milik user yang login.
     */
    public function index()
    {
        $commissions = Commission::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('commission.index', compact('commissions'));
    }

    /**
     * Form buat commission baru.
     */
    public function create()
    {
        $productTypes = [
            'hoodie'  => 'Hoodie',
            'tshirt'  => 'T-Shirt',
            'jersey'  => 'Jersey',
            'jacket'  => 'Jacket',
            'pants'   => 'Pants',
            'totebag' => 'Tote Bag',
            'other'   => 'Lainnya',
        ];

        return view('commission.create', compact('productTypes'));
    }

    /**
     * Simpan commission baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:150'],
            'product_type'    => ['required', 'string', 'in:hoodie,tshirt,jersey,jacket,pants,totebag,other'],
            'description'     => ['required', 'string', 'min:30', 'max:2000'],
            'quantity'        => ['required', 'integer', 'min:1', 'max:100'],
            'budget'          => ['nullable', 'numeric', 'min:0'],
            'reference_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'title.required'        => 'Judul commission wajib diisi.',
            'product_type.required' => 'Tipe produk wajib dipilih.',
            'product_type.in'       => 'Tipe produk tidak valid.',
            'description.required'  => 'Deskripsi wajib diisi.',
            'description.min'       => 'Deskripsi minimal 30 karakter.',
            'quantity.required'     => 'Jumlah wajib diisi.',
            'quantity.min'          => 'Jumlah minimal 1 pcs.',
            'reference_image.image' => 'File harus berupa gambar.',
            'reference_image.mimes' => 'Format: JPG, PNG, atau WEBP.',
            'reference_image.max'   => 'Ukuran gambar maksimal 5MB.',
        ]);

        // Upload gambar referensi jika ada
        $imagePath = null;
        if ($request->hasFile('reference_image')) {
            $imagePath = $request->file('reference_image')
                ->store('commissions/references', 'public');
        }

        Commission::create([
            'user_id'         => auth()->id(),
            'title'           => $validated['title'],
            'product_type'    => $validated['product_type'],
            'description'     => $validated['description'],
            'quantity'        => $validated['quantity'],
            'budget'          => $validated['budget'] ?? null,
            'reference_image' => $imagePath,
            'status'          => 'pending',
        ]);

        return redirect()->route('commission.index')
            ->with('success', 'Commission request berhasil dikirim! Tim VOID Supply akan segera menghubungimu.');
    }

    /**
     * Detail satu commission milik user.
     */
    public function show(Commission $commission)
    {
        // Pastikan commission milik user yang sedang login
        if ($commission->user_id !== auth()->id()) {
            abort(403, 'Kamu tidak berhak mengakses commission ini.');
        }

        return view('commission.show', compact('commission'));
    }

    /**
     * Batalkan commission (hanya jika masih pending).
     */
    public function destroy(Commission $commission)
    {
        if ($commission->user_id !== auth()->id()) {
            abort(403);
        }

        if ($commission->status !== 'pending') {
            return back()->with(
                'error',
                'Commission yang sudah diproses tidak dapat dibatalkan.'
            );
        }

        // Hapus gambar referensi dari storage
        if ($commission->reference_image) {
            Storage::disk('public')->delete($commission->reference_image);
        }

        $commission->delete();

        return redirect()->route('commission.index')
            ->with('success', 'Commission berhasil dibatalkan.');
    }
}