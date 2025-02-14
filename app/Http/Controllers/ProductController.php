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
        // Semua Product
        $products = Product::select(
            'products.id as id',
            'products.name as nama',
            'products.full_category_name as kategori',
            DB::raw('MIN(product_variations.price) as harga'),
            DB::raw('"Product" as type'),
            'products.updated_at as updated_at'
        )
            ->leftJoin('product_variations', 'products.id', '=', 'product_variations.product_id')
            ->groupBy('products.id', 'products.name', 'products.full_category_id');

        $services = Service::select(
            'id',
            'nama_jasa as nama',
            'kategori_jasa as kategori',
            'harga_jual as harga',
            DB::raw('"Jasa" as type'),
            'updated_at'
        );

        $productsAndServices = $products->unionAll($services);

        $semuaProduct = DB::table(DB::raw("({$productsAndServices->toSql()}) as u"))
            ->mergeBindings($productsAndServices->getQuery());

        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $type = $request->input('type');

        $kategoriList = DB::table(DB::raw("({$productsAndServices->toSql()}) as u"))
            ->mergeBindings($productsAndServices->getQuery())
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        if ($search) {
            $semuaProduct->where('nama', 'LIKE', "%{$search}%");
        }
        if ($kategori) {
            // $semuaProduct->whereRaw('JSON_CONTAINS(kategori, ?)', [$kategori]);
            $semuaProduct->where('kategori', 'LIKE', "%{$kategori}%");
        }
        if ($type) {
            $semuaProduct->where('type', $type);
        }

        $semuaProduct = $semuaProduct->orderBy('updated_at', 'desc')->paginate(10);

        // Product Analysis
        $productAnalysis = Product::select(
            'products.id as id',
            'products.name as nama_produk',
            'product_variations.price as harga',
            'product_variations.stock as stok',
            'products.sold as terjual',
            'products.review as review',
            DB::raw('"Product" as type'),
            'products.updated_at as updated_at'
        )
            ->leftJoin('product_variations', 'products.id', '=', 'product_variations.product_id')
            ->groupBy('products.id', 'products.name', 'product_variations.price', 'product_variations.stock', 'products.sold', 'products.review');

        $jasaAnalysis = Service::select(
            'id',
            'nama_jasa as nama_produk',
            'harga_jual as harga',
            DB::raw('0 as stok'),
            DB::raw('0 as terjual'),
            DB::raw('0 as review'),
            DB::raw('"Jasa" as type'),
            'updated_at'
        );

        $productAnalysis = $productAnalysis->unionAll($jasaAnalysis)->orderBy('updated_at', 'desc')->paginate(10);

        $productAnalysis->transform(function ($product) {
            $competitor = $this->tokopediaService->getTopCompetitor($product->nama_produk);
            $product->competitor = $competitor['name'];
            return $product;
        });

        return view('product.index', [
            'devices' => $semuaProduct,
            'productAnalysis' => $productAnalysis,
            'search' => $search,
            'kategori' => $kategori,
            'type' => $type,
            'kategoriList' => $kategoriList,
        ]);
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
            $product = Product::create([
                'name' => $request->name,
                'spu' => $request->spu,
                'full_category_id' => $request->full_category_id,
                'full_category_name' => $this->getNameById($this->gineeOMSService->listCategories(), $request->full_category_id),
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
                'has_variations' => (bool)$request->has_variations,
                'variant_options' => $request->variant_options,
                'images' => $request->images,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
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
                'puchase_unit' => $request->purchase_unit,
                'sales_tax_amount' => $request->sales_tax_amount,
                'remarks1' => $request->remarks1,
                'remarks2' => $request->remarks2,
                'remarks3' => $request->remarks3,
                'sold' => 0,
                'review' => 0,
            ]);

            if (!$request->has_variations) {
                $product->productVariations()->create([
                    'name' => $request->name,
                    'price' => $request->variations[0]['price'],
                    'stock' => $request->variations[0]['stock'],
                    'msku' => $request->variations[0]['msku'],
                    'barcode' => $request->variations[0]['barcode'],
                    'combinations' => null,
                ]);
            } else {
                foreach ($request->variations as $variation) {
                    $product->productVariations()->create([
                        'name' => $request->name . ' ' . $variation['name'],
                        'price' => $variation['price'],
                        'stock' => $variation['stock'],
                        'msku' => $variation['msku'],
                        'barcode' => $variation['barcode'],
                        'combinations' => json_decode($variation['combinations'], true),
                    ]);
                }
            }

            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product_images', 'public');
                    $imagePaths[] = $path;
                }
                $product->images = $imagePaths;
                $product->save();
            }

            $this->gineeOMSService->createMasterProduct($request);

            return redirect()->route('product')->with('success', 'Product telah ditambahkan!');
        }

        if ($request->type === 'Jasa') {
            Service::create([
                'nama_jasa' => $request->nama_jasa,
                'harga_beli' => $request->harga_beli,
                'kategori_jasa' => $request->kategori_jasa,
                'satuan_perhitungan' => $request->satuan_perhitungan,
                'harga_jual' => $request->harga_jual,
            ]);

            return redirect()->route('product')->with('success', 'Jasa telah ditambahkan!');
        }
    }

    public function edit($id, Request $request)
    {
        $type = $request->query('type');
        $categories = $this->gineeOMSService->listCategories();

        if ($type === 'Product') {
            $product = Product::findOrFail($id);
        } else {
            $product = Service::findOrFail($id);
        }

        return view('product.edit', compact('type', 'product', 'categories'));
    }

    public function update($id, Request $request)
    {
        if ($request->type === 'Product') {
            $product = Product::findOrFail($id);
            $product->update([
                'name' => $request->name,
                'spu' => $request->spu,
                'full_category_id' => $request->full_category_id,
                'full_category_name' => $this->getNameById($this->gineeOMSService->listCategories(), $request->full_category_id),
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
                'has_variations' => (bool)$request->has_variations,
                'variant_options' => $request->variant_options,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
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
                'puchase_unit' => $request->purchase_unit,
                'sales_tax_amount' => $request->sales_tax_amount,
                'remarks1' => $request->remarks1,
                'remarks2' => $request->remarks2,
                'remarks3' => $request->remarks3,
            ]);

            if ($request->has_variations) {
                $product->productVariations()->delete();
                foreach ($request->variations as $variation) {
                    $product->productVariations()->create([
                        'name' => $request->name . ' ' . $variation['name'],
                        'price' => $variation['price'],
                        'stock' => $variation['stock'],
                        'msku' => $variation['msku'],
                        'barcode' => $variation['barcode'],
                        'combinations' => json_decode($variation['combinations'], true),
                    ]);
                }
            } else {
                $product->productVariations()->create([
                    'name' => $request->name,
                    'price' => $request->variations[0]['price'],
                    'stock' => $request->variations[0]['stock'],
                    'msku' => $request->variations[0]['msku'],
                    'barcode' => $request->variations[0]['barcode'],
                    'combinations' => null,
                ]);
            }

            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product_images', 'public');
                    $imagePaths[] = $path;
                }
                $product->images = $imagePaths;
                $product->save();
            }

            $this->gineeOMSService->updateMasterProduct($id, $request);

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
            ->with('success', 'Device updated successfully');
    }

    public function destroy($id, Request $request)
    {
        $type = $request->query('type');

        if ($type === 'Jasa') {
            Service::findOrFail($id)->delete();
        } else {
            Product::findOrFail($id)->delete();
        }

        $this->gineeOMSService->deleteMasterProduct([$id]);

        return redirect()
            ->route('product')
            ->with('success', 'Device deleted successfully');
    }

    function getNameById($categories, $id)
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
