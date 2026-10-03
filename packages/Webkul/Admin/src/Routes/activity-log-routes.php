<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\ActivityLogController;

/**
 * Activity log routes.
 */
Route::controller(ActivityLogController::class)->prefix('activity-log')->group(function () {
    Route::get('', 'index')->name('admin.activity_log.index');

    Route::delete('{id}', 'destroy')->name('admin.activity_log.delete');

    Route::post('mass-delete', 'massDestroy')->name('admin.activity_log.mass_delete');
});
