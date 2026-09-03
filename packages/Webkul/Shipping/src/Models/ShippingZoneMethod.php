<?php

namespace Webkul\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Shipping\Contracts\ShippingZoneMethod as ShippingZoneMethodContract;

class ShippingZoneMethod extends Model implements ShippingZoneMethodContract
{
    /**
     * Flat rate method type.
     *
     * @var string
     */
    public const FLAT_RATE = 'flat_rate';

    /**
     * Free shipping method type.
     *
     * @var string
     */
    public const FREE_SHIPPING = 'free_shipping';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shipping_zone_id',
        'type',
        'title',
        'rate',
        'calculation_type',
        'sort_order',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'rate'   => 'float',
        'status' => 'boolean',
    ];

    /**
     * The zone this method belongs to.
     */
    public function zone()
    {
        return $this->belongsTo(ShippingZoneProxy::modelClass());
    }
}
