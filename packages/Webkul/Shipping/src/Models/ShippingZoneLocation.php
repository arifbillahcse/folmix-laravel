<?php

namespace Webkul\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Shipping\Contracts\ShippingZoneLocation as ShippingZoneLocationContract;

class ShippingZoneLocation extends Model implements ShippingZoneLocationContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shipping_zone_id',
        'country_code',
        'postcode',
    ];

    /**
     * The zone this location belongs to.
     */
    public function zone()
    {
        return $this->belongsTo(ShippingZoneProxy::modelClass());
    }
}
