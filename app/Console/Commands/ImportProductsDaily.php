<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\GineeOMSService;

class ImportProductsDaily extends Command
{
    protected $signature = 'import:products';
    protected $description = 'Import products daily from external source';

    public function handle()
    {
        $this->info('Importing products...');
        $gineOMSService = new GineeOMSService();
        $totalProducts = $gineOMSService->listMasterProducts()['total'];

        if ($totalProducts === 0) {
            $this->info('No products found to import.');
            return;
        }

        for ($i = 0; $i < $totalProducts; $i++) {
            $productId = $gineOMSService->listMasterProducts($i)['content'][0]['productId'];
            $data = $gineOMSService->getMasterProductDetail($productId);

            $product = Product::updateOrCreate(
                ['id' => $data['productId']],
                [
                    'id' => $data['productId'],
                    'name' => $data['name'],
                    'spu' => $data['spu'] ?? null,
                    'full_category_id' => !empty($data['fullCategoryId']) ? $data['fullCategoryId'][0] : null,
                    'full_category_name' => !empty($data['fullCategoryName']) ? $data['fullCategoryName'][0] : null,
                    'brand' => $data['brand'] ?? null,
                    'sale_status' => $data['saleStatus'] ?? null,
                    'condition' => $data['genieProductCondition'] ?? null,
                    'min_purchase' => $data['minPurchase'] ?? 1,
                    'short_description' => $data['shortDescription'] ?? null,
                    'description' => $data['description'] ?? null,
                    'images' => $data['images'],
                    'length' => $data['delivery']['length'] ?? null,
                    'width' => $data['delivery']['width'] ?? null,
                    'height' => $data['delivery']['height'] ?? null,
                    'weight' => $data['delivery']['weight'] ?? null,
                    'preorder' => $data['extraInfo']['preOrder']['settingType'] === 'PRODUCT_ON',
                    'preorder_duration' => $data['extraInfo']['preOrder']['timeToShip'] ?? null,
                    'preorder_unit' => $data['extraInfo']['preOrder']['timeUnit'] ?? null,
                    'source_url' => $data['costInfo']['sourceUrl'] ?? null,
                    'sales_tax_amount' => $data['costInfo']['salesTax']['amount'] ?? 0,
                    'remarks1' => $data['extraInfo']['additionInfo']['remark1'] ?? null,
                    'remarks2' => $data['extraInfo']['additionInfo']['remark2'] ?? null,
                    'remarks3' => $data['extraInfo']['additionInfo']['remark3'] ?? null,
                    'sold' => 0,
                    'review' => 0,
                    'created_at' => $data['createDatetime'],
                    'updated_at' => $data['updateDatetime'],
                ]
            );

            if (!empty($data['variations'])) {
                foreach ($data['variations'] as $variation) {
                    $product->productVariations()->updateOrCreate(
                        ['msku' => $variation['sku']],
                        [
                            'name' => $variation['productName'],
                            'price' => $variation['sellingPrice']['amount'],
                            'stock' => $variation['stock']['availableStock'],
                            'msku' => $variation['sku'],
                            'barcode' => $variation['barcode'] ?? '-',
                            'combinations' => null,
                        ]
                    );
                }
            }
        }

        $this->info('Product import completed successfully!');
    }
}
