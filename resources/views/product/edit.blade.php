@extends('adminlte::page')

@section('title', 'Edit Product')

@section('content_header')
    <h1>Edit Product</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form id="form" action="{{ route('product.update', ['id' => $product->id]) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="{{ request()->type }}">

                @if (request()->type === 'Product')
                    <!-- Basic Information -->
                    <h4 style="font-weight: bold; margin-top: 15px;">Basic Information</h4>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name"><span class="text-danger">*</span> Master Product Name</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Please Enter" maxlength="300" value="{{ $product->name }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="spu">SPU</label>
                                <input type="text" id="spu" name="spu" class="form-control"
                                    placeholder="Please Enter" maxlength="200" value="{{ $product->spu }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="full_category_id">Select Master Category</label>
                                <select class="form-control" id="full_category_id" name="full_category_id">
                                    <option
                                        value="{{ $product->full_category_id ? last($product->full_category_id) : '' }}">
                                        {{ $product->full_category_name ? last($product->full_category_name) : 'Select Master Category' }}
                                    </option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                        @if (!empty($category['children']))
                                            @foreach ($category['children'] as $child)
                                                <option value="{{ $child['id'] }}">&nbsp;&nbsp;{{ $child['name'] }}
                                                </option>
                                                @if (!empty($child['children']))
                                                    @foreach ($child['children'] as $subChild)
                                                        <option value="{{ $subChild['id'] }}">
                                                            &nbsp;&nbsp;&nbsp;&nbsp;{{ $subChild['name'] }}</option>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brand">Brand</label>
                                <input type="text" id="brand" name="brand" class="form-control"
                                    placeholder="1-20 digits of English, Chinese, numbers, spaces and - _ & %"
                                    maxlength="20" value="{{ $product->brand }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sale_status">Channel Selling Status</label>
                                <select class="form-control" id="sale_status" name="sale_status">
                                    <option value="FOR_SALE" {{ $product->sale_status === 'FOR_SALE' ? 'selected' : '' }}>
                                        For sale
                                    <option value="HOT_SALE" {{ $product->sale_status === 'HOT_SALE' ? 'selected' : '' }}>
                                        Hot sale
                                    <option value="NEW_ARRIVAL"
                                        {{ $product->sale_status === 'NEW_ARRIVAL' ? 'selected' : '' }}>
                                        New arrival
                                    <option value="SALE_ENDED"
                                        {{ $product->sale_status === 'SALE_ENDED' ? 'selected' : '' }}>
                                        Sale ended
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="condition">Condition</label>
                                <div style="display: flex; gap: 10px;">
                                    <label class="radio-inline">
                                        <input type="radio" name="condition" value="NEW"
                                            {{ $product->condition === 'NEW' ? 'checked' : '' }}> New
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="condition" value="USED"
                                            {{ $product->condition === 'USED' ? 'checked' : '' }}> Used
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="shelf-life">Shelf Life</label>
                                <select class="form-control" id="shelf-life" name="has_shelf_life">
                                    <option value="0" {{ $product->has_shelf_life == 0 ? 'selected' : '' }}>No Shelf
                                        Life
                                    </option>
                                    <option value="1" {{ $product->has_shelf_life == 1 ? 'selected' : '' }}>Custom
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 custom-shelf-life">
                            <div class="form-group">
                                <label for="shelf-life-duration">Shelf Life Duration</label>
                                <div class="input-group">
                                    <input type="number" id="shelf-life-duration" name="shelf_life_duration"
                                        class="form-control" placeholder="Please Enter"
                                        value="{{ $product->shelf_life_duration }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 custom-shelf-life">
                            <div class="form-group">
                                <label for="inbound-limit">Inbound Limit</label>
                                <input type="number" id="inbound-limit" name="inbound_limit" class="form-control"
                                    placeholder="Please Enter" min="0.01" max="0.99" step="0.01"
                                    value="{{ $product->inbound_limit }}">
                            </div>
                        </div>
                        <div class="col-md-2 custom-shelf-life">
                            <div class="form-group">
                                <label for="outbound-limit">Outbound Limit</label>
                                <input type="number" id="outbound-limit" name="outbound_limit" class="form-control"
                                    placeholder="Please Enter" value="{{ $product->outbound_limit }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_purchase">Minimum purchase quantity</label>
                                <input type="number" id="min_purchase" name="min_purchase" class="form-control"
                                    value="1" value="{{ $product->min_purchase }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="short_description">Short description</label>
                                <textarea id="short_description" name="short_description" class="form-control" placeholder="Please Enter"
                                    maxlength="3000">{{ $product->short_description }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="long-description">Long description</label>
                                <textarea id="long-description" class="form-control" name='description'
                                    placeholder="Type your description here and apply it to your product" maxlength="60000">{{ $product->description }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Product Information -->
                    <h4 style="font-weight: bold; margin-top: 15px;">Product Information</h4>

                    <div class="form-group d-flex align-items-center">
                        <label for="product-variations" class="mr-2">The product has variations</label>
                        <input type="checkbox" id="product-variations" name="has_variations" value="1"
                            class="form-control" style="width: auto;" onchange="toggleVariationFields()"
                            {{ $product->has_variations ? 'checked' : '' }}>
                    </div>

                    <div id="variation-fields" style="display: {{ $product->has_variations ? 'block' : 'none' }};">
                        <!-- First Variation Type -->
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="variation-type-1">Variation Type</label>
                                    <input type="text" id="variation-type-1" name="variantTypes[0][name]"
                                        class="form-control"
                                        placeholder="Enter the name of the variation, for example: color, etc."
                                        value="{{ isset($product->variant_options[0]['name']) ? $product->variant_options[0]['name'] : '' }}"
                                        oninput="generateVariationRows()">
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="variation-values-1">Option</label>
                                    <div class="tags-input-wrapper">
                                        <div class="tags-container" id="tags-container-1">
                                            @if (isset($product->variant_options[0]['values']))
                                                @foreach ($product->variant_options[0]['values'] as $value)
                                                    <span class="tag badge badge-secondary mr-1">
                                                        {{ $value }}
                                                        <span class="remove-tag"
                                                            data-index="{{ $loop->index }}">&times;</span>
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                        <input type="text" id="tag-input-1" class="form-control tag-input"
                                            placeholder="Enter a option, for example: Red, Yellow, etc">
                                        <input type="hidden" name="variantTypes[0][values]" id="values-hidden-1"
                                            value="{{ isset($product->variant_options[0]['values']) ? implode(',', $product->variant_options[0]['values']) : '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Second Variation Type -->
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="variation-type-2">Variation Type</label>
                                    <input type="text" id="variation-type-2" name="variantTypes[1][name]"
                                        class="form-control"
                                        placeholder="Enter the name of the variation, for example: size, etc."
                                        value="{{ isset($product->variant_options[1]['name']) ? $product->variant_options[1]['name'] : '' }}"
                                        oninput="generateVariationRows()">
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="variation-values-2">Option</label>
                                    <div class="tags-input-wrapper">
                                        <div class="tags-container" id="tags-container-2">
                                            @if (isset($product->variant_options[1]['values']))
                                                @foreach ($product->variant_options[1]['values'] as $value)
                                                    <span class="tag badge badge-secondary mr-1">
                                                        {{ $value }}
                                                        <span class="remove-tag"
                                                            data-index="{{ $loop->index }}">&times;</span>
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                        <input type="text" id="tag-input-2" class="form-control tag-input"
                                            placeholder="Enter a option, for example: S, M, L, etc">
                                        <input type="hidden" name="variantTypes[1][values]" id="values-hidden-2"
                                            value="{{ isset($product->variant_options[1]['values']) ? implode(',', $product->variant_options[1]['values']) : '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Variations Table -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Default Price</th>
                                <th><span class="text-danger">*</span> Available Stock</th>
                                <th><span class="text-danger">*</span> MSKU</th>
                                <th>Barcode</th>
                            </tr>
                        </thead>
                        <tbody id="variations-body">
                        </tbody>
                    </table>

                    <!-- Media Settings -->
                    <h4 style="font-weight: bold; margin-top: 15px;">Media Settings</h4>

                    <div class="form-group">
                        <label for="images">Product Image Max. 9</label>
                        @if (!empty($product->images))
                            <div id="existing-images" style="display: flex; flex-wrap: wrap; gap: 10px;">
                                @foreach ($product->images as $image)
                                    <div class="image-container" style="position: relative;">
                                        <img src="{{ Str::contains($image, 'http') ? $image : asset('storage/' . $image) }}"
                                            alt="Product Image" width="100" height="100"
                                            style="border-radius: 5px; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <input type="file" class="form-control" id="images" name="images[]" multiple
                            accept="image/*">
                    </div>

                    <!-- Delivery -->
                    <h4 style="font-weight: bold; margin-top: 15px;">Delivery</h4>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="length">Length</label>
                                <div class="input-group">
                                    <input type="number" id="length" name="length" class="form-control"
                                        placeholder="Please Enter" value="{{ $product->length }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="width">Width</label>
                                <div class="input-group">
                                    <input type="number" id="width" name="width" class="form-control"
                                        placeholder="Please Enter" value="{{ $product->width }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="height">Height</label>
                                <div class="input-group">
                                    <input type="number" id="height" name="height" class="form-control"
                                        placeholder="Please Enter" value="{{ $product->height }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="weight">Weight</label>
                                <div class="input-group">
                                    <input type="number" id="weight" name="weight" class="form-control"
                                        placeholder="Please Enter" value="{{ $product->weight }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">g</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="preorder">Preorder</label>
                        <div style="display: flex; gap: 10px;">
                            <label class="radio-inline">
                                <input type="radio" name="preorder" value="PRODUCT_OFF"
                                    {{ $product->preorder == 'PRODUCT_OFF' ? 'checked' : '' }}> No
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="preorder" value="PRODUCT_ON" id="preorder-yes"
                                    {{ $product->preorder == 'PRODUCT_ON' ? 'checked' : '' }}> Yes
                            </label>
                        </div>
                    </div>

                    <div id="preorder-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="preorder-duration">Preorder Duration</label>
                                    <input type="number" id="preorder-duration" name="preorder_duration"
                                        class="form-control" placeholder="Please Enter"
                                        value="{{ $product->preorder_duration }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="preorder-unit">Unit</label>
                                    <select class="form-control" id="preorder-unit" name="preorder_unit">
                                        <option value="WEEK" {{ $product->preorder_unit == 'WEEK' ? 'selected' : '' }}>
                                            week
                                        </option>
                                        <option value="DAY" {{ $product->preorder_unit == 'DAY' ? 'selected' : '' }}>
                                            day
                                        </option>
                                        <option value="WORK_DAY"
                                            {{ $product->preorder_unit == 'WORK_DAY' ? 'selected' : '' }}>working
                                            Days
                                        </option>
                                        <option value="HOUR" {{ $product->preorder_unit == 'HOUR' ? 'selected' : '' }}>
                                            hour
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customs Information -->
                    <h4 style="font-weight: bold; margin-top: 15px;">Customs Information</h4>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="customs-chinese-name">Customs Chinese Name</label>
                                <input type="text" id="customs-chinese-name" name="customs_chinese_name"
                                    class="form-control" placeholder="Please Enter" maxlength="200"
                                    value="{{ $product->customs_chinese_name }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="customs-english-name">Customs English Name</label>
                                <input type="text" id="customs-english-name" name="customs_english_name"
                                    class="form-control" placeholder="Please Enter" maxlength="200"
                                    value="{{ $product->customs_english_name }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="hs-code">HS Code</label>
                                <input type="text" id="hs-code" name="hs_code" class="form-control"
                                    placeholder="Please Enter" maxlength="200" value="{{ $product->hs_code }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="invoice-amount">Invoice Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" id="invoice-amount" name="invoice_amount" class="form-control"
                                        placeholder="Please Enter" value="{{ $product->invoice_amount }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="gross-weight">Gross Weight</label>
                                <div class="input-group">
                                    <input type="number" id="gross-weight" name="gross_weight" class="form-control"
                                        placeholder="Please Enter" value="{{ $product->gross_weight }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">g</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cost Information -->
                    <h4 style="font-weight: bold; margin-top: 15px;">Cost Information</h4>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="source-url">Source URL</label>
                                <input type="text" id="source-url" name="source_url" class="form-control"
                                    placeholder="Please Enter" maxlength="150" value="{{ $product->source_url }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="purchase-duration">Purchase Duration</label>
                                <input type="text" id="purchase-duration" name="purchase_duration"
                                    class="form-control" placeholder="Please Enter"
                                    value="{{ $product->purchase_duration }}">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="purchase-unit">Unit</label>
                                <select class="form-control" id="purchase-unit" name="purchase_unit"
                                    value="{{ $product->purchase_unit }}">
                                    <option value="HOUR">Hour</option>
                                    <option value="WORK_DAY">Working Days</option>
                                    <option value="DAY">Day</option>
                                    <option value="WEEK">Week</option>
                                    <option value="MONTH">Month</option>
                                </select>
                            </div>
                        </div>
                        {{-- Sales Tax Amount --}}
                        <div class="col-md-4">
                            <div class="form-group ">
                                <label for="sales-tax-amount">Sales Tax Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" id="sales-tax-amount" name="sales_tax_amount"
                                        class="form-control" placeholder="Please Enter"
                                        value="{{ $product->sales_tax_amount }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Information -->
                    <h4 style="font-weight: bold; margin-top: 15px;">Other Information</h4>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="remarks1">Remarks1</label>
                                <input type="text" id="remarks1" name="remarks1" class="form-control"
                                    placeholder="1-50 digits of English, Chinese, numbers, spaces and - _ & %"
                                    maxlength="50" value="{{ $product->remarks1 }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="remarks2">Remarks2</label>
                                <input type="text" id="remarks2" name="remarks2" class="form-control"
                                    placeholder="1-50 digits of English, Chinese, numbers, spaces and - _ & %"
                                    maxlength="50" value="{{ $product->remarks2 }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="remarks3">Remarks3</label>
                                <input type="text" id="remarks3" name="remarks3" class="form-control"
                                    placeholder="1-50 digits of English, Chinese, numbers, spaces and - _ & %"
                                    maxlength="50" value="{{ $product->remarks3 }}">
                            </div>
                        </div>
                    </div>

                    <!-- Ginee OMS -->
                    <h4 style="font-weight: bold; margin-top: 15px;">Ginee OMS</h4>

                    <div class="form-group d-flex align-items-center">
                        <label for="is-ginee" class="mr-2">{{ $product->is_ginee ? 'Update' : 'Create' }} product to
                            Ginee OMS</label>
                        <input type="checkbox" id="is-ginee" name="is_ginee" value="1" class="form-control"
                            style="width: auto;" {{ $product->is_ginee ? 'checked' : '' }}>
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nama_jasa"><span class="text-danger">*</span> Nama</label>
                                <input type="text" class="form-control" id="nama_jasa" name="nama_jasa"
                                    value="{{ $product->nama_jasa }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="harga_beli">Harga Beli</label>
                                <input type="number" class="form-control" id="harga_beli" name="harga_beli"
                                    value="{{ $product->harga_beli }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kategori_jasa">Kategori</label>
                                <input type="text" class="form-control" id="kategori_jasa" name="kategori_jasa"
                                    value="{{ $product->kategori_jasa }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="satuan_perhitungan">Satuan Perhitungan</label>
                                <input type="text" class="form-control" id="satuan_perhitungan"
                                    name="satuan_perhitungan" value="{{ $product->satuan_perhitungan }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="harga_jual">Harga Jual</label>
                                <input type="number" class="form-control" id="harga_jual" name="harga_jual"
                                    value="{{ $product->harga_jual }}">
                            </div>
                        </div>
                    </div>
                @endif

                <button id="submit" type="submit" class="btn btn-success">Update</button>
                <a href="{{ route('product.detail', ['id' => $product->id, 'type' => request()->type]) }}"
                    class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/44.2.1/ckeditor5.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/44.2.1/ckeditor5.umd.js"></script>
    <script>
        document.getElementById('form').addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
                event.preventDefault();
            }
        });

        document.getElementById('form').addEventListener('submit', function(event) {
            if (event.submitter && event.submitter.id === 'submit') {
                // Allow form submission
            } else {
                event.preventDefault();
            }
        });

        $(document).ready(function() {
            $('#full_category_id').select2({
                placeholder: "Select Master Category",
                allowClear: true
            });
        });

        function toggleShelfLife() {
            const shelfLifeSelect = document.getElementById('shelf-life');
            const customShelfLifeDivs = document.querySelectorAll('.custom-shelf-life');

            if (shelfLifeSelect.value === '1') {
                customShelfLifeDivs.forEach(div => div.style.display = 'block');
            } else {
                customShelfLifeDivs.forEach(div => div.style.display = 'none');
            }
        }

        function toggleVariationFields() {
            const has_variationsCheckbox = document.getElementById('product-variations');
            const variationFields = document.getElementById('variation-fields');

            if (has_variationsCheckbox.checked) {
                variationFields.style.display = 'block';
            } else {
                variationFields.style.display = 'none';
            }
            generateVariationRows();
        }

        function initializeTagsInput(containerId, inputId, hiddenId, initialValues = []) {
            const tagsContainer = document.getElementById(containerId);
            const tagInput = document.getElementById(inputId);
            const hiddenInput = document.getElementById(hiddenId);

            let tags = initialValues;

            function renderTags() {
                tagsContainer.innerHTML = '';
                tags.forEach((tag, index) => {
                    const tagElement = document.createElement('span');
                    tagElement.className = 'tag badge badge-secondary mr-1';
                    tagElement.innerHTML = `
                ${tag}
                <span class="remove-tag" data-index="${index}">&times;</span>
            `;
                    tagsContainer.appendChild(tagElement);
                });
                hiddenInput.value = tags.join(',');
                generateVariationRows(); // Regenerate rows when tags change
            }

            tagInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = this.value.trim();
                    if (value && !tags.includes(value)) {
                        tags.push(value);
                        renderTags();
                        this.value = '';
                    }
                }
            });

            tagsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-tag')) {
                    const index = parseInt(e.target.dataset.index);
                    tags.splice(index, 1);
                    renderTags();
                }
            });

            renderTags();
        }

        function generateVariationRows() {
            const tbody = document.getElementById('variations-body');
            const has_variations = document.getElementById('product-variations').checked;
            tbody.innerHTML = '';

            // Ambil nilai yang sudah ada dari input tersembunyi
            const existingVariations = @json($product->productVariations);
            const existingVariationsLength = existingVariations.length;

            if (!has_variations) {
                // Cari nilai default dari produk yang tidak memiliki variasi
                const defaultVariation = existingVariations[0];

                const row = document.createElement('tr');
                row.innerHTML = `
            <td>-</td>
            <td>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input type="number" name="variations[0][price]" class="form-control" placeholder="Please Enter" value="${defaultVariation.price || ''}">
                </div>
            </td>
            <td>
                <input type="number" name="variations[0][stock]" class="form-control" placeholder="Should be between 0-999,999" value="${defaultVariation.stock || ''}">
            </td>
            <td>
                <input type="text" name="variations[0][msku]" class="form-control" placeholder="Please Enter" value="${defaultVariation.msku || ''}">
            </td>
            <td>
                <input type="text" name="variations[0][barcode]" class="form-control" placeholder="Barcode only supports letters, numbers and -_" value="${defaultVariation.barcode || ''}">
            </td>
            <input type="hidden" name="variations[0][name]" value="Default">
            <input type="hidden" name="variations[0][combinations]" value='["-"]'>
        `;
                tbody.appendChild(row);
                return;
            }

            // Logika untuk produk dengan variasi
            const type1Name = document.getElementById('variation-type-1').value || '';
            const type2Name = document.getElementById('variation-type-2').value || '';
            const values1 = document.getElementById('values-hidden-1').value.split(',').filter(item => item.trim());
            const values2 = document.getElementById('values-hidden-2').value.split(',').filter(item => item.trim());

            let combinations = [];

            // Jika hanya ada satu jenis variasi
            if (values1.length && !values2.length) {
                combinations = values1.map(val1 => [val1]);
            }
            // Jika hanya ada jenis variasi kedua
            else if (!values1.length && values2.length) {
                combinations = values2.map(val2 => [val2]);
            }
            // Jika ada dua jenis variasi
            else if (values1.length && values2.length) {
                values1.forEach(val1 => {
                    values2.forEach(val2 => {
                        combinations.push([val1, val2]);
                    });
                });
            }

            let i = 0;

            combinations.forEach((combination, index) => {
                const combinationName = combination.join(' / ');

                // Cari nilai yang sudah ada berdasarkan kombinasi
                const existingVariation = existingVariations.find(variation => {
                    return JSON.stringify(variation.combinations) === JSON.stringify(combination);
                });

                const isExist = combination[1] == values2[0] && i < existingVariationsLength;

                const price = existingVariation ? existingVariation.price : (isExist ? existingVariations[i].price :
                    '');
                const stock = existingVariation ? existingVariation.stock : (isExist ? existingVariations[i].stock :
                    '');
                const msku = existingVariation ? existingVariation.msku : (isExist ? existingVariations[i].msku :
                    '');
                const barcode = existingVariation ? existingVariation.barcode : (isExist ? existingVariations[i]
                    .barcode : '');

                if (isExist) {
                    i++;
                }

                const row = document.createElement('tr');
                row.innerHTML = `
            <td>${combinationName}</td>
            <td>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input type="number" name="variations[${index}][price]" class="form-control" placeholder="Please Enter" value="${price || ''}">
                </div>
            </td>
            <td>
                <input type="number" name="variations[${index}][stock]" class="form-control" placeholder="Should be between 0-999,999" value="${stock || ''}">
            </td>
            <td>
                <input type="text" name="variations[${index}][msku]" class="form-control" placeholder="Please Enter" value="${msku || ''}">
            </td>
            <td>
                <input type="text" name="variations[${index}][barcode]" class="form-control" placeholder="Barcode only supports letters, numbers and -_" value="${barcode || ''}">
            </td>
            <input type="hidden" name="variations[${index}][name]" value="${combinationName}">
            <input type="hidden" name="variations[${index}][combinations]" value='${JSON.stringify(combination)}'>
        `;
                tbody.appendChild(row);
            });
        }

        function togglePreorderFields() {
            const preorderYes = document.getElementById('preorder-yes');
            const preorderFields = document.getElementById('preorder-fields');

            preorderFields.style.display = preorderYes.checked ? 'block' : 'none';
        }

        function updatePreorderPlaceholder() {
            const preorderUnit = document.getElementById('preorder-unit').value;
            const preorderDuration = document.getElementById('preorder-duration');

            switch (preorderUnit) {
                case 'WEEK':
                    preorderDuration.placeholder = 'Between 1-13 weeks';
                    break;
                case 'DAY':
                    preorderDuration.placeholder = 'Between 1-30 days';
                    break;
                case 'WORK_DAY':
                    preorderDuration.placeholder = 'Between 7-90 working days';
                    break;
                case 'HOUR':
                    preorderDuration.placeholder = 'Must be between 1-8 working hours';
                    break;
                default:
                    preorderDuration.placeholder = 'Please Enter';
            }
        }

        window.onload = function() {
            toggleShelfLife();
            togglePreorderFields();
            updatePreorderPlaceholder();

            const shelfLifeSelect = document.getElementById('shelf-life');
            shelfLifeSelect.addEventListener('change', toggleShelfLife);

            document.querySelectorAll('input[name="preorder"]').forEach(radio => {
                radio.addEventListener('change', togglePreorderFields);
            });
            document.getElementById('preorder-unit').addEventListener('change', updatePreorderPlaceholder);
        };

        document.addEventListener('DOMContentLoaded', function() {
            const has_variationsCheckbox = document.getElementById('product-variations');
            has_variationsCheckbox.addEventListener('change', function() {
                toggleVariationFields();
            });

            const initialValues1 = document.getElementById('values-hidden-1').value.split(',').filter(item => item
                .trim());
            const initialValues2 = document.getElementById('values-hidden-2').value.split(',').filter(item => item
                .trim());

            initializeTagsInput('tags-container-1', 'tag-input-1', 'values-hidden-1', initialValues1);
            initializeTagsInput('tags-container-2', 'tag-input-2', 'values-hidden-2', initialValues2);

            document.getElementById('variation-type-1').addEventListener('input', generateVariationRows);
            document.getElementById('variation-type-2').addEventListener('input', generateVariationRows);
            document.getElementById('values-hidden-1').addEventListener('change', generateVariationRows);
            document.getElementById('values-hidden-2').addEventListener('change', generateVariationRows);

            generateVariationRows();
        });
    </script>
    <script>
        const {
            ClassicEditor,
            Essentials,
            Bold,
            Italic,
            Font,
            Paragraph
        } = CKEDITOR;

        ClassicEditor
            .create(document.querySelector('#long-description'), {
                licenseKey: 'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3Njc5MTY3OTksImp0aSI6ImQ0YTdhMWVmLTM5MGItNGRhYi1iNTg1LWFhNmEzOWQ3YjEyMiIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiXSwiZmVhdHVyZXMiOlsiRFJVUCJdLCJ2YyI6ImI0Njc1NWU3In0.3p7AY9a3fj4AurrTrdBw_qa27RH99OoDsSj_6sK0DB1XKCyE_961SnbdkDZ5hyhdFVrtyCfoqEfPUlSb6xV_dA',
                plugins: [Essentials, Bold, Italic, Font, Paragraph],
                toolbar: [
                    'undo', 'redo', '|', 'bold', 'italic', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor'
                ]
            })
            .then(editor => {
                window.editor = editor;
            })
            .catch(error => {
                console.error(error);
            });
    </script>
@stop
