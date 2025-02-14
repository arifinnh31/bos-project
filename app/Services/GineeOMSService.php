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
        $data = [
            'name' => $request->name,
            'spu' => $request->spu,
            'fullCategoryId' => $request->full_category_id,
            'saleStatus' => $request->sale_status,
            'condition' => $request->condition,
            'shortDescription' => $request->short_description,
            'description' => $request->description,
            'images' => $request->images,
            'delivery' => [
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'weight' => $request->weight,
                'lengthUnit' => 'cm',
                'weightUnit' => 'g',
                'declareAmount' => $request->invoice_amount,
                'declareCurrency' => 'IDR',
                'declareWeight' => $request->gross_weight,
                'declareEnName' => $request->customs_english_name,
                'declareZhName' => $request->customs_chinese_name,
                'declareHsCode' => $request->hs_code,
            ],
            'type' => 'NORMAL',
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
            'extraInfo' => [
                'preOrder' => [
                    'settingType' => $request->pre_order ? 'PRODUCT_ON' : 'PRODUCT_OFF',
                    'timeToShip' => $request->preorder_duration,
                    'timeUnit' => $request->preorder_unit
                ],
                'has_shelf_life' => (bool)$request->has_shelf_life,
                'shelf_life_duration' => $request->shelf_life_duration,
                'inbound_limit' => $request->inbound_limit,
                'outbound_limit' => $request->outbound_limit,
                'additionInfo' => [
                    'remark1' => $request->remarks1,
                    'remark2' => $request->remarks2,
                    'remark3' => $request->remarks3
                ]
            ],
            'minPurchase' => $request->min_purchase,
            'brand' => $request->brand
        ];

        if ($request->has_variations) {
            $data['variantOptions'] = $request->variant_options;
            $data['variations'] = [];
            foreach ($request->variations as $variation) {
                $data['variations'][] = [
                    'optionValues' => json_decode($variation['combinations'], true),
                    'sellingPrice' => [
                        'amount' => $variation['price'],
                        'currencyCode' => 'IDR',
                    ],
                    'sku' => $variation['msku'],
                    'stock' => [
                        'availableStock' => $variation['stock'],
                        'safetyAlert' => false,
                        'safetyStock' => null,
                    ],
                    'barcode' => $variation['barcode'],
                ];
            }
        } else {
            $data['variations'] = [
                [
                    'optionValues' => ['-'],
                    'sellingPrice' => [
                        'amount' => $request->variations[0]['price'],
                        'currencyCode' => 'IDR',
                    ],
                    'sku' => $request->variations[0]['msku'],
                    'stock' => [
                        'availableStock' => $request->variations[0]['stock'],
                        'safetyAlert' => false,
                        'safetyStock' => null,
                    ],
                    'barcode' => $request->variations[0]['barcode'],
                ],
            ];
        }

        return response()->json([
            'code' => 'SUCCESS',
            'message' => '成功',
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
        $data = [
            'productId' => $id,
            'name' => $request->name,
            'spu' => $request->spu,
            'fullCategoryId' => $request->full_category_id,
            'saleStatus' => $request->sale_status,
            'condition' => $request->condition,
            'shortDescription' => $request->short_description,
            'description' => $request->description,
            'images' => $request->images,
            'delivery' => [
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'weight' => $request->weight,
                'lengthUnit' => 'cm',
                'weightUnit' => 'g',
                'declareAmount' => $request->invoice_amount,
                'declareCurrency' => 'IDR',
                'declareWeight' => $request->gross_weight,
                'declareEnName' => $request->customs_english_name,
                'declareZhName' => $request->customs_chinese_name,
                'declareHsCode' => $request->hs_code,
            ],
            'type' => 'NORMAL',
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
            'extraInfo' => [
                'preOrder' => [
                    'settingType' => $request->pre_order ? 'PRODUCT_ON' : 'PRODUCT_OFF',
                    'timeToShip' => $request->preorder_duration,
                    'timeUnit' => $request->preorder_unit
                ],
                'has_shelf_life' => (bool)$request->has_shelf_life,
                'shelf_life_duration' => $request->shelf_life_duration,
                'inbound_limit' => $request->inbound_limit,
                'outbound_limit' => $request->outbound_limit,
                'additionInfo' => [
                    'remark1' => $request->remarks1,
                    'remark2' => $request->remarks2,
                    'remark3' => $request->remarks3
                ]
            ],
            'minPurchase' => $request->min_purchase,
            'brand' => $request->brand
        ];

        if ($request->has_variations) {
            $data['variantOptions'] = $request->variant_options;
            $data['variations'] = [];
            foreach ($request->variations as $variation) {
                $data['variations'][] = [
                    'optionValues' => json_decode($variation['combinations'], true),
                    'sellingPrice' => [
                        'amount' => $variation['price'],
                        'currencyCode' => 'IDR',
                    ],
                    'sku' => $variation['msku'],
                    'stock' => [
                        'availableStock' => $variation['stock'],
                        'safetyAlert' => false,
                        'safetyStock' => null,
                    ],
                    'barcode' => $variation['barcode'],
                ];
            }
        } else {
            $data['variations'] = [
                [
                    'optionValues' => ['-'],
                    'sellingPrice' => [
                        'amount' => $request->variations[0]['price'],
                        'currencyCode' => 'IDR',
                    ],
                    'sku' => $request->variations[0]['msku'],
                    'stock' => [
                        'availableStock' => $request->variations[0]['stock'],
                        'safetyAlert' => false,
                        'safetyStock' => null,
                    ],
                    'barcode' => $request->variations[0]['barcode'],
                ],
            ];
        }

        return response()->json([
            'code' => 'SUCCESS',
            'message' => '成功',
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
            'message' => '成功',
            'data' => [
                'taskId' => "D_TASK_DELETE_MASTER_PRODUCT_1485262862233505792",
            ],
            'extra' => null,
            'pricingStrategy' => 'PAY'
        ]);

        // return $this->makeRequest('POST', '/openapi/product/master/v1/batch-delete', $productIds);
    }
}
