<?php

namespace Webkul\Product\Repositories;

use Webkul\Core\Eloquent\Repository;

class SuggestedProductRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return 'Webkul\Product\Contracts\SuggestedProduct';
    }

    /**
     * Replace the whole suggested products list, preserving the order the
     * admin arranged them in.
     *
     * @return void
     */
    public function syncProducts(array $productIds)
    {
        $this->model->newQuery()->delete();

        foreach (array_values($productIds) as $index => $productId) {
            $this->create([
                'product_id' => $productId,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Product IDs currently on the suggested products list, in the admin's
     * saved order.
     */
    public function getOrderedProductIds(): array
    {
        return $this->model->newQuery()
            ->orderBy('sort_order')
            ->pluck('product_id')
            ->all();
    }
}
