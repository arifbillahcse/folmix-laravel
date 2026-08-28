<?php

use Illuminate\Support\Facades\Route;
use Webkul\ShopExtension\Http\Controllers\Admin\PromoBannerController;

Route::group(['prefix' => config('app.admin_url')], function () {
    Route::controller(PromoBannerController::class)->prefix('settings/themes/promo-banner')->group(function () {
        Route::post('{id}', 'update')->name('admin.settings.themes.promo_banner.update');
    });
});
