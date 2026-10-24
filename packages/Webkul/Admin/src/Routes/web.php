<?php

use Illuminate\Support\Facades\Route;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;

/**
 * Auth routes.
 */
require 'auth-routes.php';

Route::group(['middleware' => ['admin', NoCacheMiddleware::class], 'prefix' => config('app.admin_url')], function () {
    /**
     * Keep-alive ping. The admin panel polls this in the background while a
     * logged-in admin has a tab open, so their session's last-activity time
     * keeps refreshing and never hits SESSION_LIFETIME - unlike customers,
     * who get no such ping and are logged out after that many idle minutes.
     * It does nothing but respond; just being an authenticated request is
     * what resets Laravel's session idle timer.
     */
    Route::get('session/keep-alive', fn () => response()->noContent())->name('admin.session.keep_alive');

    /**
     * Activity log routes.
     */
    require 'activity-log-routes.php';

    /**
     * Sales routes.
     */
    require 'sales-routes.php';

    /**
     * Catalog routes.
     */
    require 'catalog-routes.php';

    /**
     * Customers routes.
     */
    require 'customers-routes.php';

    /**
     * Marketing routes.
     */
    require 'marketing-routes.php';

    /**
     * CMS routes.
     */
    require 'cms-routes.php';

    /**
     * Reporting routes.
     */
    require 'reporting-routes.php';

    /**
     * Settings routes.
     */
    require 'settings-routes.php';

    /**
     * Configuration routes.
     */
    require 'configuration-routes.php';

    /**
     * Notification routes.
     */
    require 'notification-routes.php';

    /**
     * Help & Resources routes.
     */
    require 'help-routes.php';

    /**
     * Remaining routes.
     */
    require 'rest-routes.php';
});
