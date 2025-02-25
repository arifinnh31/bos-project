<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Services\GineeOMSService;
use App\Services\TokopediaScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $tokopediaService;
    private $gineeOMSService;

    public function __construct(GineeOMSService $gineeOMSService, TokopediaScraperService $tokopediaService)
    {
        $this->gineeOMSService = $gineeOMSService;
        $this->tokopediaService = $tokopediaService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $type = $request->input('type');

        // Semua Product
        $products = Product::select(
            'products.id as id',
            'products.type as type',
            'products.name as nama',
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(products.full_category_name, "$[0]")) as kategori'),
            DB::raw('MIN(product_variations.price) as harga'),
            'products.updated_at as updated_at'
        )
            ->leftJoin('product_variations', 'products.id', '=', 'product_variations.product_id')
            ->groupBy('products.id', 'products.name', 'products.full_category_id');

        $services = Service::select(
            'id',
            'type',
            'nama_jasa as nama',
            'kategori_jasa as kategori',
            'harga_jual as harga',
            'updated_at'
        );

        $productsAndServices = $products->unionAll($services);

        $semuaProduct = DB::table(DB::raw("({$productsAndServices->toSql()}) as u"))
            ->mergeBindings($productsAndServices->getQuery());


        if ($search) {
            $semuaProduct->where('nama', 'LIKE', "%{$search}%");
        }
        if ($kategori) {
            $semuaProduct->where('kategori', 'LIKE', "%{$kategori}%");
        }
        if ($type) {
            $semuaProduct->where('type', $type);
        }

        $semuaProduct = $semuaProduct->orderBy('updated_at', 'desc')->paginate(10);

        // Product Analysis
        $productAnalysis = Product::select(
            'products.id as id',
            'products.type as type',
            'products.name as nama_produk',
            'product_variations.price as harga',
            'product_variations.stock as stok',
            'products.sold as terjual',
            'products.review as review',
            'products.updated_at as updated_at'
        )
            ->leftJoin('product_variations', 'products.id', '=', 'product_variations.product_id')
            ->groupBy('products.id', 'products.name', 'product_variations.price', 'product_variations.stock', 'products.sold', 'products.review');

        $jasaAnalysis = Service::select(
            'id',
            'type',
            'nama_jasa as nama_produk',
            'harga_jual as harga',
            DB::raw('0 as stok'),
            DB::raw('0 as terjual'),
            DB::raw('0 as review'),
            'updated_at'
        );

        // Apply filters to productAnalysis
        if ($search) {
            $productAnalysis->where('products.name', 'LIKE', "%{$search}%");
            $jasaAnalysis->where('nama_jasa', 'LIKE', "%{$search}%");
        }
        if ($kategori) {
            $productAnalysis->where('products.full_category_name', 'LIKE', "%{$kategori}%");
            $jasaAnalysis->where('kategori_jasa', 'LIKE', "%{$kategori}%");
        }
        if ($type) {
            $productAnalysis->where('type', $type);
            $jasaAnalysis->where('type', $type);
        }

        $productAnalysis = $productAnalysis->unionAll($jasaAnalysis)->orderBy('updated_at', 'desc')->paginate(10);

        $productAnalysis->transform(function ($product) {
            $competitor = $this->tokopediaService->getTopCompetitor($product->nama_produk);
            $product->competitor = $competitor['name'];
            return $product;
        });

        $kategoriList = $this->getCategories();

        return view('product.index', compact('semuaProduct', 'productAnalysis', 'search', 'kategori', 'type', 'kategoriList'));
    }

    public function detail($id, Request $request)
    {
        $type = $request->query('type');

        if ($type === 'Product') {
            $product = Product::with('productVariations')->findOrFail($id);
            $competitor = $this->tokopediaService->getTopCompetitor($product->name);
            return view('product.detail', compact('type', 'product', 'competitor'));
        } else {
            $product = Service::findOrFail($id);
            $competitor = $this->tokopediaService->getTopCompetitor($product->nama_jasa);
            return view('product.detail', compact('type', 'product', 'competitor'));
        }
    }

    public function create()
    {
        $categories = $this->getCategories();

        return view('product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if ($request->type === 'Product') {
            $categories = $this->getCategories();
            // dd($request->all());

            // handle product
            $product = Product::create([
                'name' => $request->name,
                'is_ginee' => $request->is_ginee ? 1 : 0,
                'spu' => $request->spu,
                'full_category_id' => $this->getFullCategoryId($categories, $request->full_category_id),
                'full_category_name' => $this->getFullCategoryName($categories, $request->full_category_id),
                'brand' => $request->brand,
                'sale_status' => $request->sale_status,
                'condition' => $request->condition,
                'has_shelf_life' => (bool)$request->has_shelf_life,
                'shelf_life_duration' => $request->shelf_life_duration ? (int)$request->shelf_life_duration : null,
                'inbound_limit' => $request->inbound_limit ? ((float)$request->inbound_limit < 1 ? (float)$request->inbound_limit : 0.99) : null,
                'outbound_limit' => $request->outbound_limit ? ((float)$request->outbound_limit < 1 ? (float)$request->outbound_limit : 0.99) : null,
                'min_purchase' => (int)$request->min_purchase,
                'short_description' => $request->short_description,
                'description' => $request->description,
                'has_variations' => $request->has_variations ? 1 : 0,
                'variant_options' => $this->getVariantOptions($request),
                'length' => $request->length ? (int)$request->length : 1,
                'width' => $request->width ? (int)$request->width : 1,
                'height' => $request->height ? (int)$request->height : 1,
                'weight' => $request->weight,
                'preorder' => $request->preorder,
                'preorder_duration' => $request->preorder_duration ? (int)$request->preorder_duration : null,
                'preorder_unit' => $request->preorder_unit,
                'customs_chinese_name' => $request->customs_chinese_name,
                'customs_english_name' => $request->customs_english_name,
                'hs_code' => $request->hs_code,
                'invoice_amount' => $request->invoice_amount ? (int)$request->invoice_amount : null,
                'gross_weight' => $request->gross_weight ? (int)$request->gross_weight : null,
                'source_url' => $request->source_url,
                'purchase_duration' => $request->purchase_duration ? (int)$request->purchase_duration : null,
                'purchase_unit' => $request->purchase_unit,
                'sales_tax_amount' => $request->sales_tax_amount ? (int)$request->sales_tax_amount : null,
                'remarks1' => $request->remarks1,
                'remarks2' => $request->remarks2,
                'remarks3' => $request->remarks3,
                'sold' => 0,
                'review' => 0,
            ]);

            // handle variations
            foreach ($request->variations as $variation) {
                $product->productVariations()->create([
                    'ginee_id' => null,
                    'name' => $request->name,
                    'purchase_price' => null,
                    'price' => $variation['price'] ?? 1,
                    'stock' => $variation['stock'],
                    'msku' => $variation['msku'],
                    'barcode' => $variation['barcode'],
                    'combinations' => json_decode($variation['combinations'], true),
                ]);
            }

            // handle image
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product_images', 'public');
                    $imagePaths[] = $path;
                }
            }
            $product->update(['images' => $imagePaths]);

            // handle ginee
            if ($request->is_ginee) {
                $masterProduct = $this->gineeOMSService->createMasterProduct($product);
                // update product ginee_id
                $productId = $masterProduct['data']['productId'];
                $product->update(['ginee_id' => $productId]);
                // update variations ginee_id
                $variationIds = $masterProduct['data']['variationIds'];
                foreach ($product->productVariations as $index => $variation) {
                    $variation->update(['ginee_id' => $variationIds[$index]]);
                }
            }

            return redirect()->route('product')->with('success', 'Product created successfully');
        }

        if ($request->type === 'Jasa') {
            Service::create([
                'nama_jasa' => $request->nama_jasa,
                'harga_beli' => $request->harga_beli,
                'kategori_jasa' => $request->kategori_jasa,
                'satuan_perhitungan' => $request->satuan_perhitungan,
                'harga_jual' => $request->harga_jual,
            ]);

            return redirect()->route('product')->with('success', 'Service created successfully');
        }
    }

    public function edit($id, Request $request)
    {
        $type = $request->query('type');
        $categories = $this->getCategories();

        if ($type === 'Product') {
            $product = Product::with('productVariations')->findOrFail($id);
        } else {
            $product = Service::findOrFail($id);
        }

        return view('product.edit', compact('type', 'product', 'categories'));
    }

    public function update($id, Request $request)
    {
        if ($request->type === 'Product') {
            $product = Product::findOrFail($id);
            $categories = $this->getCategories();

            // handle product
            $product->update([
                'name' => $request->name,
                // 'is_ginee' => $request->is_ginee ? 1 : 0,
                'spu' => $request->spu,
                'full_category_id' => $this->getFullCategoryId($categories, $request->full_category_id),
                'full_category_name' => $this->getFullCategoryName($categories, $request->full_category_id),
                'brand' => $request->brand,
                'sale_status' => $request->sale_status,
                'condition' => $request->condition,
                'has_shelf_life' => (bool)$request->has_shelf_life,
                'shelf_life_duration' => $request->shelf_life_duration,
                'inbound_limit' => $request->inbound_limit,
                'outbound_limit' => $request->outbound_limit,
                'min_purchase' => $request->min_purchase,
                'short_description' => $request->short_description,
                'description' => $request->description,
                'has_variations' => $request->has_variations ? 1 : 0,
                'variant_options' => $this->getVariantOptions($request),
                'length' => $request->length ? (int)$request->length : 1,
                'width' => $request->width ? (int)$request->width : 1,
                'height' => $request->height ? (int)$request->height : 1,
                'weight' => $request->weight,
                'preorder' => $request->preorder,
                'preorder_duration' => $request->preorder_duration,
                'preorder_unit' => $request->preorder_unit,
                'customs_chinese_name' => $request->customs_chinese_name,
                'customs_english_name' => $request->customs_english_name,
                'hs_code' => $request->hs_code,
                'invoice_amount' => $request->invoice_amount,
                'gross_weight' => $request->gross_weight,
                'source_url' => $request->source_url,
                'purchase_duration' => $request->purchase_duration,
                'purchase_unit' => $request->purchase_unit,
                'sales_tax_amount' => $request->sales_tax_amount,
                'remarks1' => $request->remarks1,
                'remarks2' => $request->remarks2,
                'remarks3' => $request->remarks3,
                'sold' => 0,
                'review' => 0,
            ]);

            // handle variations
            $product->productVariations()->delete();
            foreach ($request->variations as $variation) {
                $product->productVariations()->create([
                    'ginee_id' => null,
                    'name' => $request->name,
                    'purchase_price' => null,
                    'price' => $variation['price'] ?? 1,
                    'stock' => $variation['stock'],
                    'msku' => $variation['msku'],
                    'barcode' => $variation['barcode'],
                    'combinations' => json_decode($variation['combinations'], true),
                ]);
            }

            // handle image, delete old image
            $imagePaths = [];
            if ($request->hasFile('images')) {
                if (!empty($product->images)) {
                    foreach ($product->images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product_images', 'public');
                    $imagePaths[] = $path;
                }
                $product->update(['images' => $imagePaths]);
            }

            // handle ginee
            if ($request->is_ginee) {
                if ($product->is_ginee) {
                    $this->gineeOMSService->updateMasterProduct($product);
                    // update variations ginee_id if has variations
                    $masterProduct = $this->gineeOMSService->getMasterProductDetail($product->ginee_id);
                    $variations = $masterProduct['variations'];
                    foreach ($product->productVariations as $index => $variation) {
                        $variation->update(['ginee_id' => $variations[$index]['id']]);
                    }
                } else {
                    $masterProduct = $this->gineeOMSService->createMasterProduct($product);
                    // update product ginee_id
                    $productId = $masterProduct['data']['productId'];
                    $product->update(['ginee_id' => $productId]);
                    // update variations ginee_id
                    $variationIds = $masterProduct['data']['variationIds'];
                    foreach ($product->productVariations as $index => $variation) {
                        $variation->update(['ginee_id' => $variationIds[$index]]);
                    }
                }
            }
        } else {
            $product = Service::findOrFail($id);
            $product->update([
                'nama_jasa' => $request->nama_jasa,
                'harga_beli' => $request->harga_beli,
                'kategori_jasa' => $request->kategori_jasa,
                'satuan_perhitungan' => $request->satuan_perhitungan,
                'harga_jual' => $request->harga_jual,
            ]);
        }

        return redirect()
            ->route('product.detail', ['id' => $id, 'type' => $request->type])
            ->with('success', 'Product updated successfully');
    }

    public function destroy($id, Request $request)
    {
        $type = $request->query('type');

        if ($type === 'Product') {
            $product = Product::with('productVariations')->findOrFail($id);
            $this->gineeOMSService->deleteMasterProduct([$product->ginee_id]);
            $product->productVariations()->delete();
            $product->delete();
        } else {
            $service = Service::findOrFail($id);
            $service->delete();
        }

        return redirect()
            ->route('product')
            ->with('success', 'Product deleted successfully');
    }

    /*
    * UTILITY METHODS
    */

    public function getCategories()
    {
        $json = file_get_contents(storage_path('app/categories.json'));
        $categories = json_decode($json, true);

        return $categories;
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
