<?php

namespace Webkul\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Shipping\Contracts\ShippingZone as ShippingZoneContract;

class ShippingZone extends Model implements ShippingZoneContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'is_rest_of_world',
        'sort_order',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_rest_of_world' => 'boolean',
        'status'           => 'boolean',
    ];

    /**
     * Locations belonging to this zone.
     */
    public function locations()
    {
        return $this->hasMany(ShippingZoneLocationProxy::modelClass());
    }

    /**
     * Shipping methods belonging to this zone.
     */
    public function methods()
    {
        return $this->hasMany(ShippingZoneMethodProxy::modelClass())->orderBy('sort_order');
    }
}
