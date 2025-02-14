<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Services\GineeOMSService;
use App\Services\TokopediaScraperService;

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/product', [ProductController::class, 'index'])->name('product');
Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
Route::post('/product', [ProductController::class, 'store'])->name('product.store');
Route::get('/product/{id}', [ProductController::class, 'detail'])->name('product.detail');
Route::get('/product/{id}/edit', [ProductController::class, 'edit'])->name('product.edit');
Route::put('/product/{id}', [ProductController::class, 'update'])->name('product.update');
Route::delete('/product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
Route::get('/categories', [GineeOMSService::class, 'listCategories']);
Route::get('/master-product-detail/{productId}', [GineeOMSService::class, 'getMasterProductDetail']);
Route::get('/master-products', [GineeOMSService::class, 'listMasterProducts']);
Route::get('/top-competitor/{productName}', [TokopediaScraperService::class, 'getTopCompetitor']);
