<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Resources\ProductResource;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\SuggestedProductRepository;

class SuggestedProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected SuggestedProductRepository $suggestedProductRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Show the suggested products management page (a single, standing list
     * rather than multiple records - there is only ever one /suggested
     * page to curate).
     *
     * @return View
     */
    public function edit()
    {
        $suggestedProducts = $this->getSuggestedProducts();

        return view('admin::settings.suggested-products.edit', compact('suggestedProducts'));
    }

    /**
     * Replace the suggested products list.
     */
    public function update(): JsonResponse
    {
        $this->validate(request(), [
            'product_ids'   => 'nullable|array',
            'product_ids.*' => 'required|integer',
        ]);

        $this->suggestedProductRepository->syncProducts(request()->input('product_ids', []));

        return new JsonResponse([
            'message' => trans('admin::app.settings.suggested-products.edit.update-success'),
        ]);
    }

    /**
     * Resolve the currently saved suggested products, preserving the
     * admin's saved order, so the picker can show what's already on the
     * page (image, name, price) when it loads.
     */
    protected function getSuggestedProducts(): array
    {
        $productIds = $this->suggestedProductRepository->getOrderedProductIds();

        if (empty($productIds)) {
            return [];
        }

        $products = $this->productRepository
            ->with(['images', 'inventories', 'price_indices'])
            ->findWhereIn('id', $productIds)
            ->keyBy('id');

        return ProductResource::collection(
            collect($productIds)
                ->map(fn ($productId) => $products->get($productId))
                ->filter()
                ->values()
        )->resolve();
    }
}
