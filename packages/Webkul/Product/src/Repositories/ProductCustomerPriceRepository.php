<?php

namespace Webkul\Product\Repositories;

use Webkul\Core\Eloquent\Repository;

class ProductCustomerPriceRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return 'Webkul\Product\Contracts\ProductCustomerPrice';
    }

    /**
     * Returns the fixed price this specific customer has for this product,
     * or null when no override has been set for them.
     */
    public function priceFor($product, $customerId): ?float
    {
        $price = $this->findOneWhere([
            'product_id'  => $product->id,
            'customer_id' => $customerId,
        ]);

        return $price ? (float) $price->price : null;
    }
}
