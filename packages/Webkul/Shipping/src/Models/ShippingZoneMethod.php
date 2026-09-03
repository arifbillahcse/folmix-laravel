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
     * Calculation type: fixed amount per order.
     *
     * @var string
     */
    public const PER_ORDER = 'per_order';

    /**
     * Calculation type: fixed amount per stockable item.
     *
     * @var string
     */
    public const PER_UNIT = 'per_unit';

    /**
     * Calculation type: percentage of the cart subtotal, with an
     * optional minimum fee floor (e.g. 6% of cart, never less than €12).
     *
     * @var string
     */
    public const PERCENT_OF_CART = 'percent_of_cart';

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
        'min_fee',
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
        'rate'    => 'float',
        'min_fee' => 'float',
        'status'  => 'boolean',
    ];

    /**
     * The zone this method belongs to.
     */
    public function zone()
    {
        return $this->belongsTo(ShippingZoneProxy::modelClass());
    }
}
