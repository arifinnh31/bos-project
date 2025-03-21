<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\GineeOMSService;

class ImportProducts extends Command
{
    protected $signature = 'import:products';
    protected $description = 'Import products from GineeOMS';

    public function handle()
    {
        $this->info('Importing products...');
        $gineeOMSService = new GineeOMSService();
        $categories = $gineeOMSService->listCategories();
        $totalProducts = $gineeOMSService->listMasterProducts()['total'];

        if ($totalProducts === 0) {
            $this->info('No products found to import.');
            return;
        }

        for ($i = 0; $i < 1; $i++) {
            $masterProduct = $gineeOMSService->listMasterProducts($i, 1);

            if (!isset($masterProduct['content'][0])) {
                $this->error('No product found at index ' . $i);
                continue;
            }

            $productId = $masterProduct['content'][0]['productId'];
            $data = $gineeOMSService->getMasterProductDetail($productId);

            $product = Product::updateOrCreate(
                ['ginee_id' => $data['productId']],
                [
                    'is_ginee' => true,
                    'ginee_id' => $data['productId'],
                    'name' => $data['name'],
                    'spu' => $data['spu'],
                    'full_category_id' => $data['fullCategoryId'],
                    'full_category_name' => $this->getFullCategoryName($categories, end($data['fullCategoryId'])),
                    'brand' => $data['brand'],
                    'sale_status' => $data['saleStatus'],
                    'condition' => $data['genieProductCondition'],
                    'has_shelf_life' => $data['extraInfo']['hasShelfLife'],
                    'shelf_life_duration' => $data['extraInfo']['shelfLifePeriod'],
                    'inbound_limit' => $data['extraInfo']['storageRestriction'],
                    'outbound_limit' => $data['extraInfo']['deliveryRestriction'],
                    'min_purchase' => $data['minPurchase'],
                    'short_description' => $data['shortDescription'],
                    'description' => $data['description'],
                    'variant_options' => $data['variantOptions'],
                    'has_variations' => !empty($data['variantOptions']),
                    'images' => $data['images'],
                    'length' => $data['delivery']['length'],
                    'width' => $data['delivery']['width'],
                    'height' => $data['delivery']['height'],
                    'weight' => $data['delivery']['weight'],
                    'preorder' => $data['extraInfo']['preOrder']['settingType'],
                    'preorder_duration' => $data['extraInfo']['preOrder']['timeToShip'],
                    'preorder_unit' => $data['extraInfo']['preOrder']['timeUnit'],
                    'customs_chinese_name' => $data['delivery']['declareZhName'],
                    'customs_english_name' => $data['delivery']['declareEnName'],
                    'hs_code' => $data['delivery']['declareHsCode'],
                    'invoice_amount' => $data['delivery']['declareAmount'],
                    'gross_weight' => $data['delivery']['declareWeight'],
                    'source_url' => $data['costInfo']['sourceUrl'],
                    'purchase_duration' => $data['costInfo']['purchasingTime'],
                    'purchase_unit' => $data['costInfo']['purchasingTimeUnit'],
                    'sales_tax_amount' => $data['costInfo']['salesTax']['amount'],
                    'remarks1' => $data['extraInfo']['additionInfo']['remark1'] ?? null,
                    'remarks2' => $data['extraInfo']['additionInfo']['remark2'] ?? null,
                    'remarks3' => $data['extraInfo']['additionInfo']['remark3'] ?? null,
                    'sold' => 0,
                    'review' => 0,
                    'created_at' => $data['createDatetime'],
                    'updated_at' => $data['updateDatetime'],
                ]
            );

            foreach ($data['variations'] as $variation) {
                $product->productVariations()->updateOrCreate(
                    ['ginee_id' => $variation['id']],
                    [
                        'ginee_id' => $variation['id'],
                        'name' => $variation['productName'],
                        'purchase_price' => $variation['purchasePrice'],
                        'price' => $variation['sellingPrice']['amount'],
                        'stock' => $variation['stock']['availableStock'],
                        'msku' => $variation['sku'],
                        'barcode' => $variation['barcode'],
                        'combinations' => $variation['optionValues'],
                    ]
                );
            }
        }

        $this->info('Product import completed successfully!');
    }

    public function findCategory($data, $id)
    {
        foreach ($data as $item) {
            if ($item['id'] === $id) {
                return $item;
            }

            if (!empty($item['children'])) {
                $found = $this->findCategory($item['children'], $id);
                if ($found) {
                    return $found;
                }
            }
        }
        return null;
    }

    public function getFullCategoryName($data, $id, &$result = [])
    {
        if (empty($id)) {
            return null;
        }

        $item = $this->findCategory($data, $id);

        if ($item) {
            array_unshift($result, $item['name']);

            if (isset($item['parentId']) && $item['parentId'] !== '0') {
                $this->getFullCategoryName($data, $item['parentId'], $result);
            }
        }

        return $result;
    }
}
