<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use stdClass;

class GineeOMSService
{
    private string $requestHost;
    private string $accessKey;
    private string $secretKey;

    public function __construct()
    {
        $this->requestHost = env('GINEE_API_HOST');
        $this->accessKey = env('GINEE_ACCESS_KEY');
        $this->secretKey = env('GINEE_SECRET_KEY');
    }

    private function generateAuthorization(string $httpMethod, string $requestUri): string
    {
        $signStr = $httpMethod . '$' . $requestUri . '$';
        return sprintf('%s:%s', $this->accessKey, base64_encode(hash_hmac('sha256', $signStr, $this->secretKey, true)));
    }

    private function makeRequest(string $method, string $uri, array $data = []): array
    {
        $authorization = $this->generateAuthorization($method, $uri);

        $headers = [
            'Content-Type' => 'application/json',
            'X-Advai-Country' => 'ID',
            'Authorization' => $authorization,
        ];

        $response = Http::withHeaders($headers)->{strtolower($method)}($this->requestHost . $uri, $data);

        return $response->json();
    }

    public function listCategories(): array
    {
        return $this->makeRequest('GET', '/openapi/shop/v1/categories/list')['data'];
    }

    public function getMasterProductDetail(string $productId): array
    {
        return $this->makeRequest('GET', '/openapi/product/master/v1/get', ['productId' => $productId])['data'];
    }

    public function listMasterProducts(int $page = 0, int $size = 200): array
    {
        return $this->makeRequest('POST', '/openapi/product/master/v1/list', ['page' => $page, 'size' => $size])['data'];
    }

    public function createMasterProduct(Product $product): array
    {
        $data = [
            'brand' => $product->brand,
            'type' => 'NORMAL',
            'name' => $product->name,
            'spu' => $product->spu,
            'fullCategoryId' => $product->full_category_id,
            'saleStatus' => $product->sale_status,
            'condition' => $product->condition,
            'minPurchase' => $product->min_purchase,
            'shortDescription' => $product->short_description,
            'description' => $product->description,
            'extraInfo' => [
                'hasShelfLife' => $product->has_shelf_life,
                'shelfLifePeriod' => $product->shelf_life_duration,
                'storageRestriction' => $product->inbound_limit,
                'deliveryRestriction' => $product->outbound_limit,
                'preOrder' => [
                    'settingType' => $product->preorder,
                    'timeToShip' => $product->preorder_duration,
                    'timeUnit' => $product->preorder_unit
                ],
                'additionInfo' => [
                    'remark1' => $product->remarks1,
                    'remark2' => $product->remarks2,
                    'remark3' => $product->remarks3
                ]
            ],
            'variantOptions' => $product->variant_options,
            'variations' => [],
            'images' => [],
            'delivery' => [
                'length' => $product->length,
                'lengthUnit' => 'cm',
                'width' => $product->width,
                'height' => $product->height,
                'weight' => $product->weight,
                'weightUnit' => 'g',
                'declareEnName' => $product->customs_english_name,
                'declareZhName' => $product->customs_chinese_name,
                'declareHsCode' => $product->hs_code,
                'declareCurrency' => 'IDR',
                'declareAmount' => $product->invoice_amount,
                'declareWeight' => $product->gross_weight,
                'customsWeight' => null,
            ],
            'costInfo' => [
                'sourceUrl' => $product->source_url,
                'purchasingTime' => $product->purchase_duration,
                'purchasingTimeUnit' => $product->purchase_unit,
                'salesTax' => [
                    'amount' => $product->sales_tax_amount,
                    'currencyCode' => 'IDR',
                ],
            ],
            'status' => 'PENDING_REVIEW',
        ];

        // handle images
        if (!empty($product->images)) {
            foreach ($product->images as $imagePath) {
                $image = new UploadedFile(
                    storage_path('app/public/' . $imagePath),
                    basename($imagePath)
                );
                $response = $this->uploadImage($image);
                $data['images'][] = $response['imageUrl'];
            }
        }

        // handle variations
        foreach ($product->productVariations as $variation) {
            $data['variations'][] = [
                'optionValues' => $variation['combinations'],
                'sku' => $variation['msku'],
                'barcode' => $variation['barcode'],
                'sellingPrice' => [
                    'amount' => $variation['price'],
                    'currencyCode' => 'IDR',
                ],
                'stock' => [
                    'availableStock' => $variation['stock'],
                ],
                'purchasePrice' => new stdClass(),
                'images' =>  $data['images'],
            ];
        }

        return $this->makeRequest('POST', '/openapi/product/master/v1/create', $data);
    }

    public function updateMasterProduct(Product $product): array
    {
        $data = [
            'id' => $product->ginee_id,
            'brand' => $product->brand,
            'type' => 'NORMAL',
            'name' => $product->name,
            'spu' => $product->spu,
            'fullCategoryId' => $product->full_category_id,
            'saleStatus' => $product->sale_status,
            'condition' => $product->condition,
            'minPurchase' => $product->min_purchase,
            'shortDescription' => $product->short_description,
            'description' => $product->description,
            'extraInfo' => [
                'hasShelfLife' => $product->has_shelf_life,
                'shelfLifePeriod' => $product->shelf_life_duration,
                'storageRestriction' => $product->inbound_limit,
                'deliveryRestriction' => $product->outbound_limit,
                'preOrder' => [
                    'settingType' => $product->preorder,
                    'timeToShip' => $product->preorder_duration,
                    'timeUnit' => $product->preorder_unit
                ],
                'additionInfo' => [
                    'remark1' => $product->remarks1,
                    'remark2' => $product->remarks2,
                    'remark3' => $product->remarks3
                ]
            ],
            'variantOptions' => $product->variant_options,
            'variations' => [],
            'images' => [],
            'delivery' => [
                'length' => $product->length,
                'lengthUnit' => 'cm',
                'width' => $product->width,
                'height' => $product->height,
                'weight' => $product->weight,
                'weightUnit' => 'g',
                'declareEnName' => $product->customs_english_name,
                'declareZhName' => $product->customs_chinese_name,
                'declareHsCode' => $product->hs_code,
                'declareCurrency' => 'IDR',
                'declareAmount' => $product->invoice_amount,
                'declareWeight' => $product->gross_weight,
                'customsWeight' => null,
            ],
            'costInfo' => [
                'sourceUrl' => $product->source_url,
                'purchasingTime' => $product->purchase_duration,
                'purchasingTimeUnit' => $product->purchase_unit,
                'salesTax' => [
                    'amount' => $product->sales_tax_amount,
                    'currencyCode' => 'IDR',
                ],
            ],
            'status' => 'PENDING_REVIEW',
        ];

        // handle images
        if (!empty($product->images)) {
            foreach ($product->images as $imagePath) {
                $image = new UploadedFile(
                    storage_path('app/public/' . $imagePath),
                    basename($imagePath)
                );
                $response = $this->uploadImage($image);
                $data['images'][] = $response['imageUrl'];
            }
        }

        // handle variations
        foreach ($product->productVariations as $variation) {
            $data['variations'][] = [
                'optionValues' => $variation['combinations'],
                'sku' => $variation['msku'],
                'barcode' => $variation['barcode'],
                'sellingPrice' => [
                    'amount' => $variation['price'],
                    'currencyCode' => 'IDR',
                ],
                'stock' => [
                    'availableStock' => $variation['stock'],
                ],
                'purchasePrice' => new stdClass(),
                'images' =>  $data['images'],
            ];
        }

        return $this->makeRequest('POST', '/openapi/product/master/v1/update', $data);
    }

    public function deleteMasterProduct(array $productIds)
    {
        return $this->makeRequest('POST', '/openapi/product/master/v1/batch-delete', ['productIds' => $productIds]);
    }

    public function uploadImage($image): array
    {
        $authorization = $this->generateAuthorization('POST', '/openapi/common/v1/image/upload');

        $headers = [
            'X-Advai-Country' => 'ID',
            'Authorization' => $authorization,
        ];

        $response = Http::withHeaders($headers)
            ->attach('image', file_get_contents($image), $image->getClientOriginalName())
            ->post($this->requestHost . '/openapi/common/v1/image/upload');

        return $response->json()['data'];
    }

    public function getImage(string $imageId): array
    {
        return $this->makeRequest('GET', '/openapi/common/v1/image/get', ['imageId' => $imageId])['data'];
    }
}
