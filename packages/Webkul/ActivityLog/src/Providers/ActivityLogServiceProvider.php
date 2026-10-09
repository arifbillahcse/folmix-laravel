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
     * `registerRecordable()` call here to extend coverage later.
     *
     * Uses the created()/updated()/deleted() closure hooks rather than
     * Model::observe(new RecordableObserver(...)) - observe() only keeps
     * the class name of whatever it's given, so when the event later
     * fires Laravel tries to build a *fresh* RecordableObserver through
     * the container, which has no idea what constructor arguments
     * ('Product', the name-resolver closure, etc.) to use and blows up
     * with an unresolvable-dependency error. A closure, by contrast,
     * keeps whatever instance it captured.
     */
    protected function registerModelObservers(): void
    {
        $this->registerRecordable(AdminProxy::modelClass(), new RecordableObserver(
            'Admin User',
            fn ($model) => $model->name ?? $model->email,
        ));

        $this->registerRecordable(RoleProxy::modelClass(), new RecordableObserver(
            'Role',
            fn ($model) => $model->name,
        ));

        $this->registerRecordable(ProductProxy::modelClass(), new RecordableObserver(
            'Product',
            fn ($model) => $model->sku,
        ));

        $this->registerRecordable(CategoryProxy::modelClass(), new RecordableObserver(
            'Category',
            fn ($model) => $model->name,
        ));

        $this->registerRecordable(CustomerProxy::modelClass(), new RecordableObserver(
            'Customer',
            fn ($model) => trim(($model->first_name ?? '').' '.($model->last_name ?? '')) ?: $model->email,
        ));

        $this->registerRecordable(PageProxy::modelClass(), new RecordableObserver(
            'CMS Page',
            fn ($model) => $model->page_title,
        ));

        $this->registerRecordable(ThemeCustomizationProxy::modelClass(), new RecordableObserver(
            'Theme Customization',
            fn ($model) => $model->name,
        ));

        /**
         * Orders get touched constantly by background recalculation
         * (totals, inventory, etc.) - only the status transition is worth
         * an admin's attention, so everything else is filtered out.
         */
        $this->registerRecordable(OrderProxy::modelClass(), new RecordableObserver(
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
        $this->registerRecordable(CoreConfigProxy::modelClass(), new RecordableObserver(
            'Setting',
            fn ($model) => $model->code,
            watchedAttributes: ['value'],
        ));
    }

    /**
     * Wire a single, already-configured RecordableObserver instance up to
     * a model's created/updated/deleted events via closures, which (unlike
     * Model::observe()) correctly preserve the instance's constructor
     * state - see registerModelObservers() for why this matters.
     */
    protected function registerRecordable(string $modelClass, RecordableObserver $observer): void
    {
        $modelClass::created(fn ($model) => $observer->created($model));

        $modelClass::updated(fn ($model) => $observer->updated($model));

        $modelClass::deleted(fn ($model) => $observer->deleted($model));
    }
}
