<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $hoodie = Category::where('name', 'Hoodie')->first();
        $tshirt = Category::where('name', 'T-Shirt')->first();
        $limited = Category::where('name', 'Limited')->first();

        // Produk 1 — Available
        $p1 = Product::create([
            'category_id'  => $hoodie->id,
            'name'         => 'VOID Core Hoodie Black',
            'description'  => 'Hoodie premium bahan fleece heavyweight 380gsm. Desain minimalis void aesthetic.',
            'details'      => "Material: 380gsm Fleece\nFit: Oversized\nCuci: Machine wash cold",
            'price'        => 485000,
            'stock'        => 30,
            'weight'       => 600,
            'status'       => 'available',
            'is_limited'   => false,
            'sizes'        => ['S', 'M', 'L', 'XL', 'XXL'],
        ]);

        // Produk 2 — Limited + Countdown
        $p2 = Product::create([
            'category_id'  => $limited->id,
            'name'         => 'VOID Drop-001 Collab Tee',
            'description'  => 'Limited drop pertama VOID Supply. Hanya 100 pieces worldwide.',
            'details'      => "Material: 220gsm Cotton Combed\nFit: Boxy\nJumlah: 100 pcs worldwide",
            'price'        => 350000,
            'stock'        => 100,
            'weight'       => 250,
            'status'       => 'coming_soon',
            'is_limited'   => true,
            'release_date' => now()->addDays(7),
            'sizes'        => ['S', 'M', 'L', 'XL'],
        ]);

        // Produk 3 — Preorder
        $p3 = Product::create([
            'category_id'  => $tshirt->id,
            'name'         => 'VOID Acid Wash Tee Grey',
            'description'  => 'T-shirt acid wash proses manual. Setiap piece unik.',
            'details'      => "Material: 240gsm Cotton\nFit: Regular\nNote: Setiap produk motif berbeda",
            'price'        => 275000,
            'stock'        => 50,
            'weight'       => 250,
            'status'       => 'preorder',
            'is_limited'   => false,
            'sizes'        => ['S', 'M', 'L', 'XL', 'XXL'],
        ]);

        $this->command->info('Products seeded: ' . Product::count() . ' items.');
    }
}