<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
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

    public function listMasterProducts(int $page = 0, int $size = 1): array
    {
        return $this->makeRequest('POST', '/openapi/product/master/v1/list', ['page' => $page, 'size' => $size])['data'];
    }

    public function createMasterProduct(Request $request): array
    {
        $categories = $this->listCategories();

        $data = [
            'brand' => $request->brand,
            'type' => 'NORMAL',
            'name' => $request->name,
            'spu' => $request->spu,
            'fullCategoryId' => $this->getFullCategoryId($categories, $request->full_category_id),
            'saleStatus' => $request->sale_status,
            'condition' => $request->condition,
            'minPurchase' => (int)$request->min_purchase,
            'shortDescription' => $request->short_description,
            'description' => $request->description,
            'extraInfo' => [
                'hasShelfLife' => (bool)$request->has_shelf_life,
                'shelfLifePeriod' => $request->shelf_life_duration ? (int)$request->shelf_life_duration : null,
                'storageRestriction' => $request->inbound_limit ? ((float)$request->inbound_limit < 1 ? (float)$request->inbound_limit : 0.99) : null,
                'deliveryRestriction' => $request->outbound_limit ? ((float)$request->outbound_limit < 1 ? (float)$request->outbound_limit : 0.99) : null,
                'preOrder' => [
                    'settingType' => $request->preorder,
                    'timeToShip' => $request->preorder_duration ? (int)$request->preorder_duration : null,
                    'timeUnit' => $request->preorder_unit
                ],
                'additionInfo' => [
                    'remark1' => $request->remarks1,
                    'remark2' => $request->remarks2,
                    'remark3' => $request->remarks3
                ]
            ],
            'variantOptions' => $this->getVariantOptions($request),
            'variations' => [],
            'images' => [],
            'delivery' => [
                'length' => $request->length ? (int)$request->length : 1,
                'lengthUnit' => 'cm',
                'width' => $request->width ? (int)$request->width : 1,
                'height' => $request->height ? (int)$request->height : 1,
                'weight' => $request->weight,
                'weightUnit' => 'g',
                'declareEnName' => $request->customs_english_name,
                'declareZhName' => $request->customs_chinese_name,
                'declareHsCode' => $request->hs_code,
                'declareCurrency' => 'IDR',
                'declareAmount' => $request->invoice_amount ? (int)$request->invoice_amount : null,
                'declareWeight' => $request->gross_weight ? (int)$request->gross_weight : null,
                'customsWeight' => null,
            ],
            'costInfo' => [
                'sourceUrl' => $request->source_url,
                'purchasingTime' => (int)$request->purchase_duration,
                'purchasingTimeUnit' => $request->purchase_unit,
                'salesTax' => [
                    'amount' => (int)$request->sales_tax_amount,
                    'currencyCode' => 'IDR',
                ],
            ],
            'status' => 'PENDING_REVIEW',
        ];

        // handle images
        if ($request->hasFile('images')) {
            foreach ($request->images as $image) {
                $response = $this->uploadImage($image);
                $data['images'][] = $response['data']['imageUrl'];
            }
        }

        $data['images'] = [
            "https://cdn-oss.ginee.com/api/prod/images/OPEN_API_20250221161824148_3505122485.jpg",
            "https://cdn-oss.ginee.com/api/prod/images/OPEN_API_20250221161824841_3303092257.jpg"
        ];

        // handle variations
        foreach ($request->variations as $variation) {
            $data['variations'][] = [
                'optionValues' => json_decode($variation['combinations']),
                'sku' => $variation['msku'],
                'barcode' => $variation['barcode'],
                'sellingPrice' => [
                    'amount' => (int)$variation['price'],
                    'currencyCode' => 'IDR',
                ],
                'stock' => [
                    'availableStock' => (int)$variation['stock'],
                ],
                'purchasePrice' => new stdClass(),
                'images' =>  $data['images'],
            ];
        }

        return $this->makeRequest('POST', '/openapi/product/master/v1/create', $data);
    }

    public function updateMasterProduct(string $id, Request $request): array
    {
        $categories = $this->listCategories();

        $data = [
            'id' => $id,
            'brand' => $request->brand,
            'type' => 'NORMAL',
            'name' => $request->name,
            'spu' => $request->spu,
            'fullCategoryId' => $this->getFullCategoryId($categories, $request->full_category_id),
            'saleStatus' => $request->sale_status,
            'condition' => $request->condition,
            'minPurchase' => (int)$request->min_purchase,
            'shortDescription' => $request->short_description,
            'description' => $request->description,
            'extraInfo' => [
                'hasShelfLife' => (bool)$request->has_shelf_life,
                'shelfLifePeriod' => $request->shelf_life_duration ? (int)$request->shelf_life_duration : null,
                'storageRestriction' => $request->inbound_limit ? ((float)$request->inbound_limit < 1 ? (float)$request->inbound_limit : 0.99) : null,
                'deliveryRestriction' => $request->outbound_limit ? ((float)$request->outbound_limit < 1 ? (float)$request->outbound_limit : 0.99) : null,
                'preOrder' => [
                    'settingType' => $request->preorder,
                    'timeToShip' => $request->preorder_duration ? (int)$request->preorder_duration : null,
                    'timeUnit' => $request->preorder_unit
                ],
                'additionInfo' => [
                    'remark1' => $request->remarks1,
                    'remark2' => $request->remarks2,
                    'remark3' => $request->remarks3
                ]
            ],
            'variantOptions' => $this->getVariantOptions($request),
            'variations' => [],
            'images' => [],
            'delivery' => [
                'length' => $request->length ? (int)$request->length : 1,
                'lengthUnit' => 'cm',
                'width' => $request->width ? (int)$request->width : 1,
                'height' => $request->height ? (int)$request->height : 1,
                'weight' => $request->weight,
                'weightUnit' => 'g',
                'declareEnName' => $request->customs_english_name,
                'declareZhName' => $request->customs_chinese_name,
                'declareHsCode' => $request->hs_code,
                'declareCurrency' => 'IDR',
                'declareAmount' => $request->invoice_amount ? (int)$request->invoice_amount : null,
                'declareWeight' => $request->gross_weight ? (int)$request->gross_weight : null,
                'customsWeight' => null,
            ],
            'costInfo' => [
                'sourceUrl' => $request->source_url,
                'purchasingTime' => (int)$request->purchase_duration,
                'purchasingTimeUnit' => $request->purchase_unit,
                'salesTax' => [
                    'amount' => (int)$request->sales_tax_amount,
                    'currencyCode' => 'IDR',
                ],
            ],
            'status' => 'PENDING_REVIEW',
        ];

        // handle images
        foreach ($request->images as $image) {
            $response = $this->uploadImage($image);
            $data['images'][] = $response['data']['imageUrl'];
        }

        $data['images'] = [
            "https://cdn-oss.ginee.com/api/prod/images/OPEN_API_20250221161824148_3505122485.jpg",
            "https://cdn-oss.ginee.com/api/prod/images/OPEN_API_20250221161824841_3303092257.jpg"
        ];

        // handle variations
        foreach ($request->variations as $variation) {
            $data['variations'][] = [
                'optionValues' => json_decode($variation['combinations']),
                'sku' => $variation['msku'],
                'barcode' => $variation['barcode'],
                'sellingPrice' => [
                    'amount' => (int)$variation['price'],
                    'currencyCode' => 'IDR',
                ],
                'stock' => [
                    'availableStock' => (int)$variation['stock'],
                ],
                'purchasePrice' => new stdClass(),
                'images' =>  $data['images'],
            ];
        }

        // dd($data, $this->makeRequest('POST', '/openapi/product/master/v1/update', $data));
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

        return $response->json();
    }

    public function getImage(string $imageId): array
    {
        return $this->makeRequest('GET', '/openapi/common/v1/image/get', ['imageId' => $imageId])['data'];
    }

    /*
     * UTILITY METHODS
     */

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

    public function getFullCategoryId($data, $id, &$result = [])
    {
        if (empty($id)) {
            return null;
        }

        $item = $this->findCategory($data, $id);

        if ($item) {
            array_unshift($result, $item['id']);

            if (isset($item['parentId']) && $item['parentId'] !== '0') {
                $this->getFullCategoryId($data, $item['parentId'], $result);
            }
        }

        return $result;
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

    public function getVariantOptions(Request $request): array
    {
        $variantOptions = [];

        if (!empty($request->variantTypes[0]['name']) && !empty($request->variantTypes[0]['values'])) {
            $variantOptions[] = [
                'name' => $request->variantTypes[0]['name'],
                'values' => explode(',', $request->variantTypes[0]['values']),
            ];
        }

        if (!empty($request->variantTypes[1]['name']) && !empty($request->variantTypes[1]['values'])) {
            $variantOptions[] = [
                'name' => $request->variantTypes[1]['name'],
                'values' => explode(',', $request->variantTypes[1]['values']),
            ];
        }

        return $variantOptions;
    }

    public function getNameById($categories, $id)
    {
        foreach ($categories as $item) {
            if ($item['id'] == $id) {
                return $item['name'];
            }
            if (!empty($item['children'])) {
                $result = $this->getNameById($item['children'], $id);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }
}
