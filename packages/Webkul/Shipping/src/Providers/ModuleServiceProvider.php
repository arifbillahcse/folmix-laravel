<?php

namespace Webkul\Shipping\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Shipping\Models\ShippingZone;
use Webkul\Shipping\Models\ShippingZoneLocation;
use Webkul\Shipping\Models\ShippingZoneMethod;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        ShippingZone::class,
        ShippingZoneLocation::class,
        ShippingZoneMethod::class,
    ];
}
