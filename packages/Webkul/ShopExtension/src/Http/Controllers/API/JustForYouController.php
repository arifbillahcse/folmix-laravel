<?php

namespace Webkul\ShopExtension\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Controllers\API\APIController;
use Webkul\Shop\Http\Resources\ProductCardResource;

class JustForYouController extends APIController
{
    /**
     * Create a controller instance.
     */
    public function __construct(
        protected ProductRepository $productRepository,
    ) {}

    /**
     * Get "Just for You" products based on user's viewing history or as fallback (featured).
     *
     * - If the visitor has viewed a product recently (category id stored client-side in the
     *   `last_viewed_category_id` cookie) → return featured products from that category
     * - Else, or if that category has no featured products → return featured products globally
     *   (fallback for new visitors)
     *
     * `status`/`featured`/`created_at` live on the EAV-backed `product_flat` table, not
     * directly on `products`, so filtering goes through the repository's `getAll()` (the
     * same method the storefront product API uses) instead of a plain Eloquent `where()`.
     *
     * The paginator is returned as-is (rather than just its collection) so the response carries
     * `meta.current_page` / `meta.last_page`, which the front-end "Load More" button uses to
     * fetch and append the next page over AJAX.
     *
     * API: /api/products/just-for-you
     * Query params:
     *   - category_id: override category (defaults to the cookie value)
     *   - limit: products per page (default 12 = a 3x4 grid)
     *   - page: which page to return (handled by the paginator, default 1)
     */
    public function index(Request $request): JsonResource
    {
        $limit = (int) $request->query('limit', 12);
        $categoryId = $request->query('category_id', $request->cookie('last_viewed_category_id'));

        $baseParams = [
            'status'               => 1,
            'featured'             => 1,
            'visible_individually' => 1,
            'channel_id'           => core()->getCurrentChannel()->id,
            'limit'                => $limit,
            'sort'                 => 'created_at-desc',
        ];

        $products = null;

        if ($categoryId) {
            $products = $this->productRepository
                ->getAll(array_merge($baseParams, ['category_id' => $categoryId]));
        }

        /**
         * `total()` is checked rather than the current page's emptiness so that paging past the
         * end of a category's results does not silently fall back to the global list mid-scroll.
         */
        if (
            ! $products
            || ! $products->total()
        ) {
            $products = $this->productRepository->getAll($baseParams);
        }

        return ProductCardResource::collection($products);
    }
}
