<?php

namespace Webkul\ShopExtension\Http\Controllers\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Controllers\API\APIController;
use Webkul\Shop\Http\Resources\ProductCardResource;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class FlashSaleController extends APIController
{
    /**
     * Create a controller instance.
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ThemeCustomizationRepository $themeCustomizationRepository
    ) {}

    /**
     * Hand-picked flash sale products.
     *
     * The products are chosen in the admin (Settings -> Themes, type
     * `flash_sale`), which stores only their ids in the order they were
     * dragged into. The database returns them keyed by id, so they are
     * re-sorted back into the admin's arrangement before being returned.
     */
    public function index(): JsonResource
    {
        $theme = $this->themeCustomizationRepository->findOneWhere([
            'type' => 'flash_sale',
            'status' => 1,
            'channel_id' => core()->getCurrentChannel()->id,
            'theme_code' => core()->getCurrentChannel()->theme,
        ]);

        $productIds = array_values($theme?->translate(app()->getLocale())?->options['product_ids'] ?? []);

        if (empty($productIds)) {
            return ProductCardResource::collection(collect());
        }

        $products = $this->productRepository
            ->with(['images', 'price_indices'])
            ->findWhereIn('id', $productIds)
            ->keyBy('id');

        return ProductCardResource::collection(
            collect($productIds)
                ->map(fn ($productId) => $products->get($productId))
                ->filter(fn ($product) => $product?->status)
                ->values()
        );
    }
}
