<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/community', CommunityController::class)->name('community.feed');
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/news', [NewsController::class, 'index'])->name('news');
Route::get('/news/{article}', [NewsController::class, 'show'])->name('news.show');
Route::get('/faq', [HelpController::class, 'faq'])->name('faq');
Route::get('/bonus', [HelpController::class, 'bonus'])->name('bonus');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/account', AccountController::class)->name('account');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::post('/bonus', [HelpController::class, 'redeem'])->name('bonus.redeem');
    Route::get('/locks/{product}/pay', [PurchaseController::class, 'checkout'])->name('locks.pay');
    Route::post('/locks/{product}/request', [PurchaseController::class, 'store'])->name('locks.request');
    Route::post('/purchases/{purchase}/cash-out', [PurchaseController::class, 'cashOut'])->name('purchases.cash-out');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::put('/payment-methods', [AdminController::class, 'updatePaymentMethods'])->name('payment-methods.update');
        Route::post('/purchases/{purchase}/activate', [AdminController::class, 'activate'])->name('activate');
        Route::post('/purchases/{purchase}/reject', [AdminController::class, 'reject'])->name('reject');
        Route::post('/clients/{user}/locks', [AdminController::class, 'addLock'])->name('add-lock');
        Route::post('/withdrawals/{withdrawal}', [AdminController::class, 'settle'])->name('settle');
        Route::post('/bonuses/{redemption}', [AdminController::class, 'settleBonus'])->name('bonuses.settle');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/news/create', [AdminNewsController::class, 'create'])->name('news.create');
        Route::post('/news', [AdminNewsController::class, 'store'])->name('news.store');
        Route::get('/news/{article}/edit', [AdminNewsController::class, 'edit'])->name('news.edit');
        Route::put('/news/{article}', [AdminNewsController::class, 'update'])->name('news.update');
        Route::delete('/news/{article}', [AdminNewsController::class, 'destroy'])->name('news.destroy');
    });
});
