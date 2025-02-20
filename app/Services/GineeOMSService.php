<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

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

    public function createMasterProduct(Request $request): JsonResponse
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
            'minPurchase' => $request->min_purchase,
            'shortDescription' => $request->short_description,
            'description' => $request->description,
            'extraInfo' => [
                'hasShelfLife' => (bool)$request->has_shelf_life,
                'shelfLifePeriod' => $request->shelf_life_duration,
                'storageRestriction' => $request->inbound_limit,
                'deliveryRestriction' => $request->outbound_limit,
                'preOrder' => [
                    'settingType' => $request->pre_order,
                    'timeToShip' => $request->preorder_duration,
                    'timeUnit' => $request->preorder_unit
                ],
                'additionInfo' => [
                    'remark1' => $request->remarks1,
                    'remark2' => $request->remarks2,
                    'remark3' => $request->remarks3
                ]
            ],
            'images' => $request->images,
            'delivery' => [
                'length' => $request->length,
                'lengthUnit' => 'cm',
                'width' => $request->width,
                'height' => $request->height,
                'weight' => $request->weight,
                'weightUnit' => 'g',
                'declareEnName' => $request->customs_english_name,
                'declareZhName' => $request->customs_chinese_name,
                'declareHsCode' => $request->hs_code,
                'declareCurrency' => 'IDR',
                'declareAmount' => $request->invoice_amount,
                'declareWeight' => $request->gross_weight,
            ],
            'costInfo' => [
                'sourceUrl' => $request->source_url,
                'purchasingTime' => $request->purchase_duration,
                'purchasingTimeUnit' => $request->purchase_unit,
                'salesTax' => [
                    'amount' => $request->sales_tax_amount,
                    'currencyCode' => 'IDR',
                ],
            ],
            'status' => 'PENDING_REVIEW',
        ];

        if ($request->has_variations) {
            $data['variantOptions'] = [];
            foreach ($request->variant_options as $option) {
                $data['variantOptions'][] = [
                    'name' => $option['name'],
                    'values' => $option['values'],
                ];
            }
            $data['variations'] = [];
            foreach ($request->variations as $variation) {
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
                    'purchasePrice' => [],
                    'images' => [],
                ];
            }
            
        } else {
            $data['variantOptions'] = [];
            $data['variations'] = [
                [
                    'optionValues' => ['-'],
                    'sku' => $request->variations[0]['msku'],
                    'barcode' => $request->variations[0]['barcode'],
                    'sellingPrice' => [
                        'amount' => $request->variations[0]['price'],
                        'currencyCode' => 'IDR',
                    ],
                    'stock' => [
                        'availableStock' => $request->variations[0]['stock'],
                    ],
                    'purchasePrice' => [],
                ],
            ];
        }

        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'OK',
            'data' => [
                'success' => true,
                'productId' => 'MP61ED3452E21B840001BBDE33',
                'variationIds' => [
                    'MV61ED3452E21B840001BBDE34'
                ],
                'invalidFields' => null
            ],
            'extra' => null,
            'pricingStrategy' => 'PAY'
        ]);

        // return $this->makeRequest('POST', '/openapi/product/master/v1/create', $data);
    }

    public function updateMasterProduct(string $id, Request $request): JsonResponse
    {
        $categories = $this->listCategories();

        $data = [
            'productId' => $id,
            'brand' => $request->brand,
            'type' => 'NORMAL',
            'name' => $request->name,
            'spu' => $request->spu,
            'fullCategoryId' => $this->getFullCategoryId($categories, $request->full_category_id),
            'saleStatus' => $request->sale_status,
            'condition' => $request->condition,
            'minPurchase' => $request->min_purchase,
            'shortDescription' => $request->short_description,
            'description' => $request->description,
            'extraInfo' => [
                'hasShelfLife' => (bool)$request->has_shelf_life,
                'shelfLifePeriod' => $request->shelf_life_duration,
                'storageRestriction' => $request->inbound_limit,
                'deliveryRestriction' => $request->outbound_limit,
                'preOrder' => [
                    'settingType' => $request->pre_order,
                    'timeToShip' => $request->preorder_duration,
                    'timeUnit' => $request->preorder_unit
                ],
                'additionInfo' => [
                    'remark1' => $request->remarks1,
                    'remark2' => $request->remarks2,
                    'remark3' => $request->remarks3
                ]
            ],
            'images' => $request->images,
            'delivery' => [
                'length' => $request->length,
                'lengthUnit' => 'cm',
                'width' => $request->width,
                'height' => $request->height,
                'weight' => $request->weight,
                'weightUnit' => 'g',
                'declareEnName' => $request->customs_english_name,
                'declareZhName' => $request->customs_chinese_name,
                'declareHsCode' => $request->hs_code,
                'declareCurrency' => 'IDR',
                'declareAmount' => $request->invoice_amount,
                'declareWeight' => $request->gross_weight,
            ],
            'costInfo' => [
                'sourceUrl' => $request->source_url,
                'purchasingTime' => $request->purchase_duration,
                'purchasingTimeUnit' => $request->purchase_unit,
                'salesTax' => [
                    'amount' => $request->sales_tax_amount,
                    'currencyCode' => 'IDR',
                ],
            ],
            'status' => 'PENDING_REVIEW',
        ];

        if ($request->has_variations) {
            $data['variantOptions'] = [];
            foreach ($request->variant_options as $option) {
                $data['variantOptions'][] = [
                    'name' => $option['name'],
                    'values' => $option['values'],
                ];
            }
            $data['variations'] = [];
            foreach ($request->variations as $variation) {
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
                    'purchasePrice' => [],
                    'images' => [],
                ];
            }
        } else {
            $data['variantOptions'] = [];
            $data['variations'] = [
                [
                    'optionValues' => ['-'],
                    'sku' => $request->variations[0]['msku'],
                    'barcode' => $request->variations[0]['barcode'],
                    'sellingPrice' => [
                        'amount' => $request->variations[0]['price'],
                        'currencyCode' => 'IDR',
                    ],
                    'stock' => [
                        'availableStock' => $request->variations[0]['stock'],
                    ],
                    'purchasePrice' => [],
                ],
            ];
        }

        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'OK',
            'data' => [
                'success' => true,
                'productId' => 'MP61ED3452E21B840001BBDE33',
                'variationIds' => [
                    'MV61ED3452E21B840001BBDE34'
                ],
                'invalidFields' => null
            ],
            'extra' => null,
            'pricingStrategy' => 'PAY'
        ]);

        // return $this->makeRequest('POST', '/openapi/product/master/v1/update', $data);
    }

    public function deleteMasterProduct(array $productIds): JsonResponse
    {
        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'OK',
            'data' => [
                'taskId' => "D_TASK_DELETE_MASTER_PRODUCT_1485262862233505792",
            ],
            'extra' => null,
            'pricingStrategy' => 'PAY'
        ]);

        // return $this->makeRequest('POST', '/openapi/product/master/v1/batch-delete', $productIds);
    }

    // function getNameById($categories, $id)
    // {
    //     foreach ($categories as $item) {
    //         if ($item['id'] == $id) {
    //             return $item['name'];
    //         }
    //         if (!empty($item['children'])) {
    //             $result = $this->getNameById($item['children'], $id);
    //             if ($result !== null) {
    //                 return $result;
    //             }
    //         }
    //     }
    //     return null;
    // }

    function findCategory($data, $id)
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

    function getFullCategoryId($data, $id, &$result = [])
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

    function getFullCategoryName($data, $id, &$result = [])
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
