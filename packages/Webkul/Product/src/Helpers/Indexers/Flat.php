<?php

namespace Webkul\Product\Helpers\Indexers;

use Illuminate\Support\Facades\Schema;
use Webkul\Product\Contracts\Product;
use Webkul\Product\Helpers\ProductType;
use Webkul\Product\Repositories\ProductFlatRepository;
use Webkul\Product\Repositories\ProductRepository;

class Flat extends AbstractIndexer
{
    /**
     * @var int
     */
    private $batchSize;

    /**
     * Attribute codes that can be fill during flat creation.
     *
     * @var string[]
     */
    protected $fillableAttributeCodes = [
        'sku',
        'name',
        'price',
        'weight',
        'status',
    ];

    /**
     * @var array
     */
    protected $flatColumns = [];

    /**
     * Structural/relational product_flat columns that must only ever come from
     * $product itself. A family attribute sharing one of these codes (e.g. an
     * admin naming a custom attribute "type", "channel" or "status") must never
     * be allowed to drive it - see the guard in updateOrCreate().
     *
     * @var string[]
     */
    protected $reservedFlatColumns = [
        'id',
        'sku',
        'type',
        'created_at',
        'updated_at',
        'locale',
        'channel',
        'attribute_family_id',
        'product_id',
        'parent_id',
    ];

    /**
     * Channels
     *
     * @var array
     */
    protected $channels = [];

    /**
     * Family Attributes
     *
     * @var array
     */
    protected $familyAttributes = [];

    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductFlatRepository $productFlatRepository
    ) {
        $this->batchSize = self::BATCH_SIZE;

        $this->flatColumns = Schema::getColumnListing('product_flat');
    }

    /**
     * Reindex all products
     *
     * @return void
     */
    public function reindexFull()
    {
        while (true) {
            $paginator = $this->productRepository
                ->with([
                    'variants',
                    'attribute_family',
                    'attribute_values',
                    'variants.attribute_family',
                    'variants.attribute_values',
                ])
                ->cursorPaginate($this->batchSize);

            $this->reindexBatch($paginator->items());

            if (! $cursor = $paginator->nextCursor()) {
                break;
            }

            request()->query->add(['cursor' => $cursor->encode()]);
        }

        request()->query->remove('cursor');
    }

    /**
     * Reindex products by batch size
     *
     * @return void
     */
    public function reindexBatch($products)
    {
        foreach ($products as $product) {
            $this->refresh($product);
        }
    }

    /**
     * Refresh product flat indices
     *
     * @param  Product  $product
     * @return void
     */
    public function refresh($product)
    {
        $this->updateOrCreate($product);

        if (! ProductType::hasVariants($product->type)) {
            return;
        }

        foreach ($product->variants()->get() as $variant) {
            $this->updateOrCreate($variant);
        }
    }

    /**
     * Creates product flat
     *
     * @param  Product  $product
     * @return void
     */
    public function updateOrCreate($product)
    {
        $familyAttributes = $this->getCachedFamilyAttributes($product);

        $channelIds = $product->channels->pluck('id')->toArray();

        if (empty($channelIds)) {
            $channelIds[] = core()->getDefaultChannel()->id;
        }

        $attributeValues = $product->attribute_values()->get();

        foreach (core()->getAllChannels() as $channel) {
            if (in_array($channel->id, $channelIds)) {
                foreach ($channel->locales as $locale) {
                    /**
                     * product_flat.parent_id is a foreign key to another
                     * product_flat row's own id (not to products.id) - it must
                     * be the specific flat row of the parent PRODUCT for this
                     * same channel/locale pair, not the parent's product id.
                     * refresh() always flat-indexes a configurable parent
                     * before its variants, so that row already exists by the
                     * time a variant gets here; if it genuinely doesn't yet
                     * (e.g. this variant was reached first in a full reindex
                     * pass), leave it null rather than violating the FK -
                     * the next reindex fills it in once the parent exists.
                     */
                    $parentFlatId = $product->parent_id
                        ? $this->productFlatRepository->findOneWhere([
                            'product_id' => $product->parent_id,
                            'channel' => $channel->code,
                            'locale' => $locale->code,
                        ])?->id
                        : null;

                    $productFlat = $this->productFlatRepository->updateOrCreate([
                        'product_id' => $product->id,
                        'channel' => $channel->code,
                        'locale' => $locale->code,
                    ], [
                        'type' => $product->type,
                        'sku' => $product->sku,
                        'attribute_family_id' => $product->attribute_family_id,
                        'parent_id' => $parentFlatId,
                    ]);

                    foreach ($familyAttributes as $attribute) {
                        /**
                         * A custom attribute whose code happens to collide with one of
                         * product_flat's structural columns (e.g. an admin naming an
                         * attribute "type", "sku", "status", or "channel") would
                         * otherwise have its selected value silently overwrite that
                         * column here - corrupting the product's real type/sku/etc. on
                         * every reindex. These columns are only ever set from $product
                         * itself, never from a family attribute's value.
                         */
                        if (
                            ! in_array($attribute->code, $this->flatColumns)
                            || in_array($attribute->code, $this->reservedFlatColumns)
                        ) {
                            continue;
                        }

                        $productAttributeValues = $attributeValues->where('attribute_id', $attribute->id);

                        if ($attribute->value_per_channel) {
                            if ($attribute->value_per_locale) {
                                $productAttributeValues = $productAttributeValues
                                    ->where('channel', $channel->code)
                                    ->where('locale', $locale->code);
                            } else {
                                $productAttributeValues = $productAttributeValues->where('channel', $channel->code);
                            }
                        } else {
                            if ($attribute->value_per_locale) {
                                $productAttributeValues = $productAttributeValues->where('locale', $locale->code);
                            }
                        }

                        $productAttributeValue = $productAttributeValues->first();

                        $productFlat->{$attribute->code} = $productAttributeValue[$attribute->column_name] ?? null;
                    }

                    $productFlat->save();
                }
            } else {
                if (request()->route()?->getName() == 'admin.catalog.products.update') {
                    $this->productFlatRepository->deleteWhere([
                        'product_id' => $product->id,
                        'channel' => $channel->code,
                    ]);
                }
            }
        }
    }

    /**
     * @param  Product  $product
     * @return mixed
     */
    public function getCachedFamilyAttributes($product)
    {
        if (array_key_exists($product->attribute_family_id, $this->familyAttributes)) {
            return $this->familyAttributes[$product->attribute_family_id];
        }

        return $this->familyAttributes[$product->attribute_family_id] = $product->attribute_family->custom_attributes;
    }
}
