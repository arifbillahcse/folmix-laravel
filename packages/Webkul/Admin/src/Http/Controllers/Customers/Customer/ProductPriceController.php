<?php

namespace Webkul\Admin\Http\Controllers\Customers\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Product\Repositories\ProductCustomerPriceRepository;

class ProductPriceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected ProductCustomerPriceRepository $productCustomerPriceRepository) {}

    /**
     * Store a customer's custom price for a product. Re-setting a price on
     * a product that already has one for this customer just updates it,
     * rather than erroring on the unique (product_id, customer_id) pair.
     */
    public function store(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'price'      => 'required|numeric|min:0',
        ]);

        Event::dispatch('customer.product_price.create.before');

        $existing = $this->productCustomerPriceRepository->findOneWhere([
            'product_id'  => $data['product_id'],
            'customer_id' => $id,
        ]);

        $productPrice = $existing
            ? $this->productCustomerPriceRepository->update(['price' => $data['price']], $existing->id)
            : $this->productCustomerPriceRepository->create([
                'product_id'  => $data['product_id'],
                'customer_id' => $id,
                'price'       => $data['price'],
            ]);

        Event::dispatch('customer.product_price.create.after', $productPrice);

        return new JsonResponse([
            'message' => trans('admin::app.customers.customers.view.special-pricing.create-success'),
            'data'    => $productPrice->load('product'),
        ]);
    }

    /**
     * Update a customer's custom price for a product.
     */
    public function update(int $id, int $priceId, Request $request): JsonResponse
    {
        $data = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $productPrice = $this->productCustomerPriceRepository->findOneWhere([
            'id'          => $priceId,
            'customer_id' => $id,
        ]);

        abort_if(! $productPrice, 404);

        Event::dispatch('customer.product_price.update.before', $priceId);

        $productPrice = $this->productCustomerPriceRepository->update($data, $productPrice->id);

        Event::dispatch('customer.product_price.update.after', $productPrice);

        return new JsonResponse([
            'message' => trans('admin::app.customers.customers.view.special-pricing.update-success'),
            'data'    => $productPrice->load('product'),
        ]);
    }

    /**
     * Remove a customer's custom price for a product.
     */
    public function destroy(int $id, int $priceId): JsonResponse
    {
        $productPrice = $this->productCustomerPriceRepository->findOneWhere([
            'id'          => $priceId,
            'customer_id' => $id,
        ]);

        abort_if(! $productPrice, 404);

        Event::dispatch('customer.product_price.delete.before', $priceId);

        $this->productCustomerPriceRepository->delete($productPrice->id);

        Event::dispatch('customer.product_price.delete.after', $priceId);

        return new JsonResponse([
            'message' => trans('admin::app.customers.customers.view.special-pricing.delete-success'),
        ]);
    }
}
