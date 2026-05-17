<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Hoodie',   'description' => 'Hoodie streetwear premium dari VOID Supply.'],
            ['name' => 'T-Shirt',  'description' => 'Kaos streetwear dengan desain eksklusif.'],
            ['name' => 'Jersey',   'description' => 'Jersey limited edition kolaborasi.'],
            ['name' => 'Limited',  'description' => 'Koleksi limited drop eksklusif VOID.'],
            ['name' => 'Custom',   'description' => 'Produk hasil commission & custom order.'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}