<?php

namespace Webkul\ActivityLog\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\ActivityLog\Listeners\LogAuthEvents;
use Webkul\ActivityLog\Observers\RecordableObserver;
use Webkul\CMS\Models\PageProxy;
use Webkul\Category\Models\CategoryProxy;
use Webkul\Core\Models\CoreConfigProxy;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Product\Models\ProductProxy;
use Webkul\Sales\Models\OrderProxy;
use Webkul\Theme\Models\ThemeCustomizationProxy;
use Webkul\User\Models\AdminProxy;
use Webkul\User\Models\RoleProxy;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register package services into the container.
     */
    public function register(): void
    {
        $this->app->singleton(LogAuthEvents::class);
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->registerAuthListeners();

        $this->registerModelObservers();
    }

    /**
     * Admin/customer login, logout, and failed-login attempts.
     */
    protected function registerAuthListeners(): void
    {
        Event::listen(Login::class, [LogAuthEvents::class, 'handleLogin']);

        Event::listen(Logout::class, [LogAuthEvents::class, 'handleLogout']);

        Event::listen(Failed::class, [LogAuthEvents::class, 'handleFailed']);
    }

    /**
     * Who created/updated/deleted the records that matter most for a store
     * owner auditing staff activity. Deliberately a bounded, high-value
     * set rather than every model in the system - add another
     * `::observe()` line here to extend coverage later.
     */
    protected function registerModelObservers(): void
    {
        AdminProxy::modelClass()::observe(new RecordableObserver(
            'Admin User',
            fn ($model) => $model->name ?? $model->email,
        ));

        RoleProxy::modelClass()::observe(new RecordableObserver(
            'Role',
            fn ($model) => $model->name,
        ));

        ProductProxy::modelClass()::observe(new RecordableObserver(
            'Product',
            fn ($model) => $model->sku,
        ));

        CategoryProxy::modelClass()::observe(new RecordableObserver(
            'Category',
            fn ($model) => $model->name,
        ));

        CustomerProxy::modelClass()::observe(new RecordableObserver(
            'Customer',
            fn ($model) => trim(($model->first_name ?? '').' '.($model->last_name ?? '')) ?: $model->email,
        ));

        PageProxy::modelClass()::observe(new RecordableObserver(
            'CMS Page',
            fn ($model) => $model->page_title,
        ));

        ThemeCustomizationProxy::modelClass()::observe(new RecordableObserver(
            'Theme Customization',
            fn ($model) => $model->name,
        ));

        /**
         * Orders get touched constantly by background recalculation
         * (totals, inventory, etc.) - only the status transition is worth
         * an admin's attention, so everything else is filtered out.
         */
        OrderProxy::modelClass()::observe(new RecordableObserver(
            'Order',
            fn ($model) => $model->increment_id,
            watchedAttributes: ['status'],
        ));

        /**
         * General settings (Admin > Settings > General, including the
         * Footer Content fields) are stored as individual key/value rows
         * rather than one record per "setting", so the config key itself
         * is the readable name.
         */
        CoreConfigProxy::modelClass()::observe(new RecordableObserver(
            'Setting',
            fn ($model) => $model->code,
            watchedAttributes: ['value'],
        ));
    }
}
