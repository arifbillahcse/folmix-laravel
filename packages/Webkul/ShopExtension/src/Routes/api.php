<?php

use Illuminate\Support\Facades\Route;
use Webkul\ShopExtension\Http\Controllers\API\FlashSaleController;
use Webkul\ShopExtension\Http\Controllers\API\JustForYouController;

Route::group(['prefix' => 'api'], function () {
    Route::controller(FlashSaleController::class)->prefix('products')->group(function () {
        Route::get('flash-sale', 'index')->name('shop.api.products.flash_sale.index');
    });

    Route::controller(JustForYouController::class)->prefix('products')->group(function () {
        Route::get('just-for-you', 'index')->name('shop.api.products.just_for_you.index');
    });
});
