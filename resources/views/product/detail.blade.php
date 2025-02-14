@extends('adminlte::page')

@section('title', 'Detail Product')

@section('content_header')
    <h1>Detail Product</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-2">
                    @php
                        $image = 'https://www.protrusmoto.com/wp-content/uploads/2015/04/placeholder-200x200.png';
                        if ($type === 'Product' && !empty($product->images)) {
                            $image = Str::contains($product->images[0], 'http')
                                ? $product->images[0]
                                : asset('storage/' . $product->images[0]);
                        }
                    @endphp
                    <img src="{{ $image }}" alt="Product Image" class="img-fluid rounded">
                </div>
                <div class="col-md-8">
                    <h2>{{ $product->name ?? $product->nama_jasa }}</h2>
                    <h4 class="mt-3">
                        IDR
                        {{ number_format($type === 'Product' ? $product->productVariations->first()->price : $product->harga_jual, 0, ',', '.') }}
                    </h4>
                </div>
                <div class="col-md-2">
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('product.edit', ['id' => $product->id, 'type' => request()->type]) }}"
                            class="btn btn-secondary mr-2">Edit</a>
                        <button class="btn btn-danger" data-toggle="modal" data-target="#deleteConfirmModal">Hapus</button>
                    </div>
                </div>
            </div>

            <!-- Modal Konfirmasi Hapus -->
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog"
                aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus Produk</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda yakin ingin menghapus produk ini?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <form action="{{ route('product.destroy', ['id' => $product->id, 'type' => request()->type]) }}"
                                method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="detailTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="detail-tab" data-toggle="tab" href="#detail" role="tab">Detail
                        Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="analysis-tab" data-toggle="tab" href="#analysis" role="tab">Analisis
                        Produk</a>
                </li>
            </ul>

            <!-- Tab content -->
            <div class="tab-content mt-3">

                <!-- Detail Produk -->
                <div class="tab-pane fade show active" id="detail" role="tabpanel">
                    <div class="mb-4">
                        <p>SKU : {{ $type === 'Product' ? $product->productVariations->first()->msku ?? '-' : '-' }}</p>
                        <p>Kondisi : {{ $type === 'Product' ? ($product->condition === 'NEW' ? 'Baru' : 'Bekas') : '-' }}</p>
                        <p>Brand : {{ $product->brand ?? '-' }}</p>
                        <p>Jumlah Pembelian Minimum : {{ $product->min_purchase ?? '-' }}</p>
                    </div>
                    <div class="mb-4">
                        <p class="font-weight-bold">Deskripsi</p>
                        {!! $type === 'Product' ? ($product->description != '<p></p>' ? $product->description : '-') : '-' !!}
                    </div>
                </div>

                <!-- Analisis Produk -->
                <div class="tab-pane fade" id="analysis" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="bg-light font-weight-bold" style="width: 200px;">Nama Produk</td>
                                    <td class="font-weight-bold">{{ $product->name ?? $product->nama_jasa }}</td>
                                    <td class="font-weight-bold">{{ $competitor['name'] ?? 'Tidak ada kompetitor' }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light">Harga</td>
                                    <td>{{ 'Rp' . number_format($type === 'Product' ? $product->productVariations->first()->price : $product->harga_jual, 0, ',', '.') }}</td>
                                    <td>{{ $competitor['price'] ? 'Rp' . number_format($competitor['price'], 0, ',', '.') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light">Review</td>
                                    <td>{{ $product->review ?? '0' }}</td>
                                    <td>{{ $competitor['review'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light">Terjual</td>
                                    <td>{{ $product->sold ?? '0' }}</td>
                                    <td>{{ $competitor['sold'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light">Selisih Harga</td>
                                    <td colspan="2">
                                        @if ($competitor['price'])
                                            @php
                                                $harga =
                                                    $type === 'Product'
                                                        ? $product->productVariations->first()->price
                                                        : $product->harga_jual;
                                                $selisihHarga = $harga - $competitor['price'];
                                                $textColor = $selisihHarga > 0 ? 'danger' : 'success';
                                            @endphp
                                            <span class="text-{{ $textColor }}">
                                                Rp{{ number_format(abs($selisihHarga), 0, ',', '.') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bg-light">Selisih Review</td>
                                    <td colspan="2">
                                        @if ($competitor['review'])
                                            @php
                                                $selisihReview = $product->review - $competitor['review'];
                                                $textColor = $selisihReview > 0 ? 'danger' : 'success';
                                            @endphp
                                            <span class="text-{{ $textColor }}">
                                                {{ abs($selisihReview) }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bg-light">Selisih Terjual</td>
                                    <td colspan="2">
                                        @if ($competitor['sold'])
                                            @php
                                                $selisihTerjual = $product->sold - $competitor['sold'];
                                                $textColor = $selisihTerjual > 0 ? 'danger' : 'success';
                                            @endphp
                                            <span class="text-{{ $textColor }}">
                                                {{ abs($selisihTerjual) }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
