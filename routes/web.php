<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Security\ChallengeController;
use App\Http\Controllers\Security\DeviceController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/community', CommunityController::class)->name('community.feed');
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/news', [NewsController::class, 'index'])->name('news');
Route::get('/news/{article}', [NewsController::class, 'show'])->name('news.show');
Route::get('/faq', [HelpController::class, 'faq'])->name('faq');
Route::get('/bonus', [BonusController::class, 'index'])->name('bonus');
Route::get('/bonus/{code}', [BonusController::class, 'show'])->name('bonus.claim');
Route::get('/team', TeamController::class)->name('team');
Route::get('/media/{path}', MediaController::class)->where('path', '.*')->name('media.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/account', AccountController::class)->name('account');

Route::get('/security/devices/enroll', [DeviceController::class, 'enroll'])->name('security.devices.enroll');
Route::post('/security/devices', [DeviceController::class, 'store'])->name('security.devices.store');
Route::get('/security/challenge/{challenge}/wait', [ChallengeController::class, 'wait'])->name('security.challenge.wait');
Route::get('/security/challenge/{challenge}/status', [ChallengeController::class, 'status'])->name('security.challenge.status');
Route::post('/security/challenge/{challenge}/complete', [ChallengeController::class, 'complete'])->name('security.challenge.complete');
Route::get('/security/review/{challenge}', [ChallengeController::class, 'review'])->name('security.review');
Route::post('/security/review/{challenge}', [ChallengeController::class, 'decide'])->name('security.challenge.decide');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::get('/security/devices', [DeviceController::class, 'index'])->name('security.devices');
    Route::delete('/security/devices/{device}', [DeviceController::class, 'destroy'])->name('security.devices.destroy');
    Route::delete('/security/sessions/{session}', [DeviceController::class, 'revokeSession'])->name('security.sessions.destroy');
    Route::post('/bonus/redeem', [BonusController::class, 'claim'])->name('bonus.redeem');
    Route::get('/locks/{product}/pay', [PurchaseController::class, 'checkout'])->name('locks.pay');
    Route::post('/locks/{product}/request', [PurchaseController::class, 'store'])->name('locks.request');
    Route::post('/purchases/{purchase}/cash-out', [PurchaseController::class, 'cashOut'])->name('purchases.cash-out');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::put('/payment-methods', [AdminController::class, 'updatePaymentMethods'])->name('payment-methods.update');
        Route::post('/purchases/{purchase}/activate', [AdminController::class, 'activate'])->name('activate');
        Route::post('/purchases/{purchase}/reject', [AdminController::class, 'reject'])->name('reject');
        Route::post('/clients/{user}/locks', [AdminController::class, 'addLock'])->name('add-lock');
        Route::post('/clients/{user}/credit', [AdminController::class, 'creditWallet'])->name('credit');
        Route::post('/withdrawals/{withdrawal}', [AdminController::class, 'settle'])->name('settle');
        Route::post('/bonuses', [AdminController::class, 'storeBonusCode'])->name('bonuses.store');
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
