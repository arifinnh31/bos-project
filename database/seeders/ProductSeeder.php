<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data untuk produk "MacBook Pro M2 16GB/512GB"
        $product = Product::create([
            'name' => 'MacBook Pro M2 16GB/512GB',
            'spu' => 'MBP-M2-16-512',
            'full_category_id' => '100644',
            'full_category_name' => 'Computers & Accessories',
            'brand' => 'Apple',
            'sale_status' => 'FOR_SALE',
            'condition' => 'NEW',
            'has_shelf_life' => false,
            'shelf_life_duration' => null,
            'inbound_limit' => null,
            'outbound_limit' => null,
            'min_purchase' => 1,
            'short_description' => 'MacBook Pro dengan chip M2, 16GB RAM, dan 512GB SSD.',
            'description' => 'MacBook Pro dengan chip M2 yang powerful, 16GB RAM untuk multitasking, dan 512GB SSD untuk penyimpanan cepat.',
            'has_variations' => false,
            'variant_options' => null,
            'images' => ['product_images/89a2e2d8-f77d-4ce8-bb02-2f03bbbe53b2.jpg'],
            'length' => 30,
            'width' => 21,
            'height' => 1.5,
            'weight' => 1500,
            'preorder' => false,
            'preorder_duration' => null,
            'preorder_unit' => null,
            'customs_chinese_name' => 'MacBook Pro M2 16GB/512GB',
            'customs_english_name' => 'MacBook Pro M2 16GB/512GB',
            'hs_code' => '84713000',
            'invoice_amount' => 25000000,
            'gross_weight' => 1600,
            'source_url' => 'https://www.apple.com/macbook-pro',
            'purchase_duration' => 7,
            'purchase_unit' => 'DAY',
            'sales_tax_amount' => 2500000,
            'remarks1' => 'Barang baru, garansi resmi 1 tahun',
            'remarks2' => 'Dikirim dari gudang Jakarta',
            'remarks3' => 'Stok terbatas',
            'sold' => 85,
            'review' => 5,
        ]);

        ProductVariation::create([
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => 25000000,
            'stock' => 50,
            'msku' => $product->spu,
            'barcode' => '1234567890123',
            'combinations' => null,
        ]);
    }
}
