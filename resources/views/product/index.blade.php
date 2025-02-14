@extends('adminlte::page')

@section('title', 'List Product')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>List Product</h1>
        <button onclick="window.location.href='{{ route('product.create') }}'" class="btn btn-primary">Create New
            Product</button>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('product') }}" class="d-flex justify-content-end align-items-center mb-3">
                <div class="form-group mr-2">
                    <input type="text" name="search" class="form-control" placeholder="Search..."
                        value="{{ request('search') }}">
                </div>
                <div class="form-group mr-2">
                    <select name="kategori" class="form-control">
                        <option value="" selected>Pilih Kategori</option>
                        @foreach ($kategoriList as $kategoriOption)
                            <option value="{{ $kategoriOption }}">{{ $kategoriOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-2">
                    <select name="type" class="form-control">
                        <option value="">Pilih Type</option>
                        <option value="Product" {{ request('type') == 'Product' ? 'selected' : '' }}>Product</option>
                        <option value="Jasa" {{ request('type') == 'Jasa' ? 'selected' : '' }}>Jasa</option>
                    </select>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </form>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="semua-tab" data-toggle="tab" href="#semua" role="tab">Semua
                        Product</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="analysis-tab" data-toggle="tab" href="#analysis" role="tab">Product
                        Analysis</a>
                </li>
            </ul>

            <!-- Tab content -->
            <div class="tab-content" id="myTabContent">
                <!-- Tab Semua Product -->
                <div class="tab-pane fade show active" id="semua" role="tabpanel">
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Type</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($devices as $index => $device)
                                <tr>
                                    <td>{{ $devices->firstItem() + $index }}</td>
                                    <td>
                                        <a
                                            href="{{ route('product.detail', ['id' => $device->id, 'type' => $device->type]) }}">
                                            {{ $device->nama }}
                                        </a>
                                    </td>
                                    <td>{{ $device->kategori }}</td>
                                    <td>{{ $device->type }}</td>
                                    <td>Rp{{ number_format($device->harga, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Paginasi -->
                    {{ $devices->links('vendor.pagination.default') }}
                </div>

                <!-- Tab Product Analysis  -->
                <div class="tab-pane fade" id="analysis" role="tabpanel">
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Review</th>
                                <th>Terjual</th>
                                <th>Kompetitor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productAnalysis as $index => $product)
                                <tr>
                                    <td>{{ $productAnalysis->firstItem() + $index }}</td>
                                    <td>
                                        <a
                                            href="{{ route('product.detail', ['id' => $product->id, 'type' => $product->type]) }}">
                                            {{ $product->nama_produk }}
                                        </a>
                                    </td>
                                    <td>Rp{{ number_format($product->harga, 0, ',', '.') }}</td>
                                    <td>{{ $product->stok }}</td>
                                    <td>{{ $product->review }}</td>
                                    <td>{{ $product->terjual }}</td>
                                    <td>
                                        @if ($product->competitor)
                                            {{ $product->competitor }}
                                        @else
                                            <span class="text-muted">Tidak ada kompetitor</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Paginasi -->
                    {{ $productAnalysis->links('vendor.pagination.default') }}
                </div>
            </div>
        </div>
    </div>
@stop
