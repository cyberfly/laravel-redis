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
                'name' => 'Laptop Pro',
                'sku' => 'LAP-PRO-001',
                'description' => 'High-performance laptop for professionals',
                'price' => 1299.99,
                'stock' => 50,
                'category' => 'Electronics'
            ],
            [
                'name' => 'Wireless Mouse',
                'sku' => 'ACC-MOU-001',
                'description' => 'Ergonomic wireless mouse',
                'price' => 29.99,
                'stock' => 100,
                'category' => 'Accessories'
            ],
            [
                'name' => 'External SSD',
                'sku' => 'STO-SSD-001',
                'description' => '1TB External Solid State Drive',
                'price' => 159.99,
                'stock' => 75,
                'category' => 'Storage'
            ],
            [
                'name' => 'Gaming Monitor',
                'sku' => 'MON-GAM-001',
                'description' => '27" 144Hz Gaming Monitor',
                'price' => 349.99,
                'stock' => 30,
                'category' => 'Electronics'
            ],
            [
                'name' => 'Mechanical Keyboard',
                'sku' => 'ACC-KEY-001',
                'description' => 'RGB Mechanical Gaming Keyboard',
                'price' => 129.99,
                'stock' => 60,
                'category' => 'Accessories'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
