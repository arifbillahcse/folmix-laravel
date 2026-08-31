<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Product\Contracts\ProductCustomerPrice as ProductCustomerPriceContract;

class ProductCustomerPrice extends Model implements ProductCustomerPriceContract
{
    /**
     * Add fillable property to the model.
     *
     * @var array
     */
    protected $fillable = [
        'price',
        'product_id',
        'customer_id',
    ];

    /**
     * Get the product that owns the customer price.
     */
    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }

    /**
     * Get the customer that owns the customer price.
     */
    public function customer()
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
