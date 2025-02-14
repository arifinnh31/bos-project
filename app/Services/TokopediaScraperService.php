<?php

namespace App\Services;

class TokopediaScraperService
{
    private $baseUrl = 'https://gql.tokopedia.com/graphql/SearchProductQueryV4';

    public function getTopCompetitor($productName)
    {
        $header = [
            'authority: gql.tokopedia.com',
            'accept: */*',
            'accept-language: en-US,en;q=0.9',
            'content-type: application/json',
            'cookie: bm_sz=1E836F185136E04E74B51EF86E5BDF0D~YAAQrO84F6cvDqSFAQAAqM4nvRJ4T99ecI0m0pa5qvWMwVcejybNmPGjX2S4l5Z8wgiEcYP91OKTzUDvK+imI1QaRyKsW6TXjmoGgZYU7P7P9MkMleJmSDIwtHIEeAi4eYFReuLCvQrXcEqFR6MS4UWVXUQb6etuobmBxWscyeD7xYDiLpfosZlMkMfg73LeeeQq7znLHk9tCw8KK76egznjcMkS/FLZdc7WwFqoCMSKO8k507nJWAzW3NGmBC9uusoisgQ/zqhc9ICWU7gecWUgbXIo/lEccZ8Dtx/FFz6NdvnR5nM=~4473657~3618104; _abck=4BB94791FDA138839DBD53D376980B58~0~YAAQrO84F7AvDqSFAQAAwtEnvQnPuKPW1+x0m7YyBDCuLoTMT61FlSnzyfxR19SiDp5+FQ84es5OIPLlEFU6h6KOYOEbdtfanzvLg8RMvULumin7nVDn1QyA1XsUkWMVi67PsLdxHbUZCUZzol07tUzJjRMgEoM8t8URu/Bh8ZR9aj4gSXKjI1fFZCuaKyErdxbXOzLFJQEwZ12rXHC6WeD/CSnOFXBSr6+TrWjYrrfBMW0D78xtuiD7oZRyplNSiLMXuw2jMvgm1ncrB4KbR9SJaVvYaU1H9baK4tPXh7SXCx6onnhRkD2MD4JmHwhXT2SzxeCdDDe2HGvIHpkeL6cDtaO/U2WLWvkHjjtn7mI+YH3sWI8xLGJM9+5MvyPEZf+vSMH0nqHX28NEBx9eV+JlvFhqesoKS99f~-1~-1~-1;',
            'origin: https://www.tokopedia.com',
            'referer: https://www.tokopedia.com/search?st=product&q=' . urlencode($productName) . '&srp_component_id=02.01.00.00&srp_page_id=&srp_page_title=&navsource=',
            'sec-ch-ua: "Not?A_Brand";v="8", "Chromium";v="108", "Google Chrome";v="108"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: cors',
            'sec-fetch-site: same-site',
            'tkpd-userid: 0',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
            'x-device: desktop-0.0',
            'x-source: tokopedia-lite',
            'x-tkpd-lite-service: zeus',
            'x-version: 68ba647',
        ];

        $query = '[{"operationName":"SearchProductQueryV4","variables":{"params":"device=desktop&navsource=&ob=23&page=1&q=' . urlencode($productName) . '&related=true&rows=60&safe_search=false&scheme=https&shipping=&source=search&srp_component_id=02.01.00.00&srp_page_id=&srp_page_title=&st=product&start=0&topads_bucket=true&unique_id=6003aca41edf75450ef4ee2ca669b8f6&user_addressId=&user_cityId=176&user_districtId=2274&user_id=&user_lat=&user_long=&user_postCode=&user_warehouseId=12210375&variants="},"query":"query SearchProductQueryV4($params: String\u0021) {\\n  ace_search_product_v4(params: $params) {\\n    header {\\n      totalData\\n      totalDataText\\n      processTime\\n      responseCode\\n      errorMessage\\n      additionalParams\\n      keywordProcess\\n      componentId\\n      __typename\\n    }\\n    data {\\n      banner {\\n        position\\n        text\\n        imageUrl\\n        url\\n        componentId\\n        trackingOption\\n        __typename\\n      }\\n      backendFilters\\n      isQuerySafe\\n      ticker {\\n        text\\n        query\\n        typeId\\n        componentId\\n        trackingOption\\n        __typename\\n      }\\n      redirection {\\n        redirectUrl\\n        departmentId\\n        __typename\\n      }\\n      related {\\n        position\\n        trackingOption\\n        relatedKeyword\\n        otherRelated {\\n          keyword\\n          url\\n          product {\\n            id\\n            name\\n            price\\n            imageUrl\\n            rating\\n            countReview\\n            url\\n            priceStr\\n            wishlist\\n            shop {\\n              city\\n              isOfficial\\n              isPowerBadge\\n              __typename\\n            }\\n            ads {\\n              adsId: id\\n              productClickUrl\\n              productWishlistUrl\\n              shopClickUrl\\n              productViewUrl\\n              __typename\\n            }\\n            badges {\\n              title\\n              imageUrl\\n              show\\n              __typename\\n            }\\n            ratingAverage\\n            labelGroups {\\n              position\\n              type\\n              title\\n              url\\n              __typename\\n            }\\n            componentId\\n            __typename\\n          }\\n          componentId\\n          __typename\\n        }\\n        __typename\\n      }\\n      suggestion {\\n        currentKeyword\\n        suggestion\\n        suggestionCount\\n        instead\\n        insteadCount\\n        query\\n        text\\n        componentId\\n        trackingOption\\n        __typename\\n      }\\n      products {\\n        id\\n        name\\n        ads {\\n          adsId: id\\n          productClickUrl\\n          productWishlistUrl\\n          productViewUrl\\n          __typename\\n        }\\n        badges {\\n          title\\n          imageUrl\\n          show\\n          __typename\\n        }\\n        category: departmentId\\n        categoryBreadcrumb\\n        categoryId\\n        categoryName\\n        countReview\\n        customVideoURL\\n        discountPercentage\\n        gaKey\\n        imageUrl\\n        labelGroups {\\n          position\\n          title\\n          type\\n          url\\n          __typename\\n        }\\n        originalPrice\\n        price\\n        priceRange\\n        rating\\n        ratingAverage\\n        shop {\\n          shopId: id\\n          name\\n          url\\n          city\\n          isOfficial\\n          isPowerBadge\\n          __typename\\n        }\\n        url\\n        wishlist\\n        sourceEngine: source_engine\\n        __typename\\n      }\\n      violation {\\n        headerText\\n        descriptionText\\n        imageURL\\n        ctaURL\\n        ctaApplink\\n        buttonText\\n        buttonType\\n        __typename\\n      }\\n      __typename\\n    }\\n    __typename\\n  }\\n}"}]';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (!isset($data[0]['data']['ace_search_product_v4']['data']['products']) || empty($data[0]['data']['ace_search_product_v4']['data']['products'])) {
            return [
                'name' => null,
                'price' => null,
                'review' => null,
                'sold' => null,
            ];
        }

        $products = $data[0]['data']['ace_search_product_v4']['data']['products'];
        $product = $products[0] ?? null;

        $name = $product['name'] ?? null;
        $price = isset($product['price']) ? (int)preg_replace('/[^\d]/', '', $product['price']) : null;
        $review = $product['countReview'] ?? null;
        $sold = isset(end($products[0]['labelGroups'])['title']) ? (int)preg_replace('/[^\d]/', '', end($products[0]['labelGroups'])['title']) : null;

        return [
            'name' => $name,
            'price' => $price,
            'review' => $review,
            'sold' => $sold,
        ];
    }
}
