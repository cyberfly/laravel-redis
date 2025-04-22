<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Gaming Laptop',
                'description' => 'High-performance gaming laptop with RTX 4080',
                'price' => 1999.99,
                'stock' => 10,
                'sku' => 'LAP-GAM-001',
                'is_active' => true
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with long battery life',
                'price' => 49.99,
                'stock' => 100,
                'sku' => 'MOU-WIR-001',
                'is_active' => true
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB mechanical keyboard with Cherry MX switches',
                'price' => 129.99,
                'stock' => 50,
                'sku' => 'KEY-MEC-001',
                'is_active' => true
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
