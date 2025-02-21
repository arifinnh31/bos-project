<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Services\GineeOMSService;
use App\Services\TokopediaScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $kategoriList = $this->gineeOMSService->listCategories();

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
        $categories = $this->gineeOMSService->listCategories();

        return view('product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if ($request->type === 'Product') {
            $masterProduct = $this->gineeOMSService->createMasterProduct($request);
            $productId = $masterProduct['data']['productId'];
            // dd($masterProduct, $productId, $request->all());
            $data = $this->gineeOMSService->getMasterProductDetail($productId);
            $categories = $this->gineeOMSService->listCategories();

            $product = Product::create(
                [
                    'ginee_id' => $data['productId'],
                    'name' => $data['name'],
                    'spu' => $data['spu'],
                    'full_category_id' => $data['fullCategoryId'],
                    'full_category_name' => $this->gineeOMSService->getFullCategoryName($categories, end($data['fullCategoryId'])),
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
                    'variantOptions' => $data['variantOptions'],
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
                    'remarks1' => $data['extraInfo']['additionInfo']['remark1'],
                    'remarks2' => $data['extraInfo']['additionInfo']['remark2'],
                    'remarks3' => $data['extraInfo']['additionInfo']['remark3'],
                    'sold' => 0,
                    'review' => 0,
                    'created_at' => $data['createDatetime'],
                    'updated_at' => $data['updateDatetime'],
                ]
            );

            foreach ($data['variations'] as $variation) {
                $product->productVariations()->create(
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
        $categories = $this->gineeOMSService->listCategories();

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
            $masterProduct = $this->gineeOMSService->updateMasterProduct($product->ginee_id, $request);
            // dd($masterProduct);
            $data = $this->gineeOMSService->getMasterProductDetail($product->ginee_id);
            $categories = $this->gineeOMSService->listCategories();

            $product->update(
                [
                    'ginee_id' => $data['productId'],
                    'name' => $data['name'],
                    'spu' => $data['spu'],
                    'full_category_id' => $data['fullCategoryId'],
                    'full_category_name' => $this->gineeOMSService->getFullCategoryName($categories, end($data['fullCategoryId'])),
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
                    'variantOptions' => $data['variantOptions'],
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

            $product->productVariations()->delete();

            foreach ($data['variations'] as $variation) {
                $product->productVariations()->create(
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
}
