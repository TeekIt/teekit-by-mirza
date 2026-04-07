<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VanProduct;

class VanProductSeeder extends Seeder
{
    /**
     * Seed the van_products table with sample data
     */
    public function run(): void
    {
        VanProduct::insert([
            [
                'seller_id' => 13,
                'category_id' => 6,
                'van_id' => 2,
                'product_name' => 'Pipe A',
                'sku' => 'PIPE-A-001',
                'price' => 150.5,
                'featured' => 1,
                'discount_percentage' => '10%',
                'weight' => 1.5,
                'brand' => 'BrandX',
                'size' => 'M',
                'status' => 'active',
                'contact' => '03001234567',
                'colors' => json_encode(['red', 'blue']),
                'bike' => 0,
                'car' => 0,
                'van' => 1,
                'feature_img' => 'path/to/image.jpg',
                'height' => 10,
                'width' => 5,
                'length' => 20,
                'job_reference' => 'JOB123',
                'quantity' => 60,
                'min_threshold' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'seller_id' => 14,
                'category_id' => 4,
                'van_id' => 2,
                'product_name' => 'Valve B',
                'sku' => 'VALVE-B-001',
                'price' => 200,
                'featured' => 0,
                'discount_percentage' => '5%',
                'weight' => 2,
                'brand' => 'BrandY',
                'size' => 'L',
                'status' => 'active',
                'contact' => '03001234567',
                'colors' => json_encode(['black']),
                'bike' => 0,
                'car' => 1,
                'van' => 1,
                'feature_img' => 'path/to/image2.jpg',
                'height' => 12,
                'width' => 6,
                'length' => 25,
                'job_reference' => 'JOB124',
                'quantity' => 30,
                'min_threshold' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'seller_id' => 15,
                'category_id' => 2,
                'van_id' => 2,
                'product_name' => 'Fitting C',
                'sku' => 'FIT-C-001',
                'price' => 80,
                'featured' => 0,
                'discount_percentage' => '0%',
                'weight' => 1,
                'brand' => 'BrandZ',
                'size' => 'S',
                'status' => 'active',
                'contact' => '03001234567',
                'colors' => json_encode(['white']),
                'bike' => 1,
                'car' => 0,
                'van' => 1,
                'feature_img' => 'path/to/image3.jpg',
                'height' => 8,
                'width' => 4,
                'length' => 15,
                'job_reference' => 'JOB125',
                'quantity' => 0,
                'min_threshold' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}