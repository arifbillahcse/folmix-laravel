<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Product\Contracts\SuggestedProduct as SuggestedProductContract;

class SuggestedProduct extends Model implements SuggestedProductContract
{
    /**
     * Add fillable property to the model.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'sort_order',
    ];

    /**
     * Get the suggested product's underlying product.
     */
    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }
}
