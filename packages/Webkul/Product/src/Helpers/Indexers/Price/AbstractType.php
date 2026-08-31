<?php

namespace Webkul\Product\Helpers\Indexers\Price;

use Illuminate\Support\Carbon;
use Webkul\CatalogRule\Repositories\CatalogRuleProductPriceRepository;
use Webkul\Core\Contracts\Channel;
use Webkul\Customer\Contracts\CustomerGroup;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Contracts\Product;
use Webkul\Product\Repositories\ProductCustomerGroupPriceRepository;

abstract class AbstractType
{
    /**
     * Product instance.
     *
     * @var Product
     */
    protected $product;

    /**
     * Channel instance.
     *
     * @var Channel
     */
    protected $channel;

    /**
     * Customer Group instance.
     *
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected ProductCustomerGroupPriceRepository $productCustomerGroupPriceRepository,
        protected CatalogRuleProductPriceRepository $catalogRuleProductPriceRepository
    ) {}

    /**
     * Set current product
     *
     * @param  Product  $product
     * @return AbstractPriceIndex
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Set channel
     *
     * @param  Channel  $channel
     * @return AbstractPriceIndex
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set customer group
     *
     * @param  CustomerGroup  $customerGroup
     * @return AbstractPriceIndex
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * Returns product specific pricing for customer group
     *
     * @return array
     */
    public function getIndices()
    {
        return [
            'min_price' => ($minPrice = $this->getMinimalPrice()) ?? 0,
            'regular_min_price' => $this->product->price ?? 0,
            'max_price' => $minPrice ?? 0,
            'regular_max_price' => $this->product->price ?? 0,
            'product_id' => $this->product->id,
            'channel_id' => $this->channel->id,
            'customer_group_id' => $this->customerGroup->id,
        ];
    }

    /**
     * Get product minimal price.
     *
     * @param  int  $qty
     * @return float
     */
    public function getMinimalPrice($qty = null)
    {
        /**
         * A customer group price that was explicitly configured for this
         * product/group is an intentional fixed price - it must be shown
         * as-is, even when it's higher than the base/special/catalog-rule
         * price. Only fall through to the normal cheapest-price logic when
         * no group-specific price applies.
         */
        $customerGroupPrice = $this->getCustomerGroupPrice($qty ?? 1);

        if ($customerGroupPrice !== null) {
            return $customerGroupPrice;
        }

        $rulePrice = $this->getCatalogRulePrice();

        if (
            empty($this->product->special_price)
            && empty($rulePrice)
        ) {
            return $this->product->price;
        }

        if (! (float) $this->product->special_price) {
            if ($rulePrice) {
                $discountedPrice = min($rulePrice->price, $this->product->price);
            } else {
                $discountedPrice = $this->product->price;
            }
        } else {
            if ($rulePrice) {
                if (
                    core()->isChannelDateInInterval(
                        $this->product->special_price_from,
                        $this->product->special_price_to
                    )
                ) {
                    $discountedPrice = min($rulePrice->price, $this->product->special_price);
                } else {
                    $discountedPrice = $rulePrice->price;
                }
            } else {
                if (
                    core()->isChannelDateInInterval(
                        $this->product->special_price_from,
                        $this->product->special_price_to
                    )
                ) {
                    $discountedPrice = $this->product->special_price;
                } else {
                    $discountedPrice = $this->product->price;
                }
            }
        }

        return $discountedPrice;
    }

    /**
     * Get product group price. Returns null when no customer group price
     * has been configured for this product/group/quantity, so the caller
     * can fall back to normal pricing instead of treating "no override" as
     * "override with the base price" (which used to make min() always win).
     *
     * @param  int  $qty
     * @return float|null
     */
    public function getCustomerGroupPrice($qty)
    {
        $customerGroupPrices = $this->productCustomerGroupPriceRepository
            ->prices($this->product, $this->customerGroup->id);

        if ($customerGroupPrices->isEmpty()) {
            return null;
        }

        $lastQty = 1;

        $matchedPrice = null;

        foreach ($customerGroupPrices as $customerGroupPrice) {
            if (
                $customerGroupPrice->qty > $qty
                || $customerGroupPrice->qty < $lastQty
            ) {
                continue;
            }

            if ($customerGroupPrice->value_type == 'discount') {
                if (
                    $customerGroupPrice->value >= 0
                    && $customerGroupPrice->value <= 100
                ) {
                    $matchedPrice = $this->product->price - ($this->product->price * $customerGroupPrice->value) / 100;

                    $lastQty = $customerGroupPrice->qty;
                }
            } else {
                /**
                 * Fixed prices are taken as-is - including when they're
                 * higher than the base price - so a group can be given a
                 * markup, not just a discount.
                 */
                if ($customerGroupPrice->value >= 0) {
                    $matchedPrice = $customerGroupPrice->value;

                    $lastQty = $customerGroupPrice->qty;
                }
            }
        }

        return $matchedPrice;
    }

    /**
     * Get catalog rules product price for specific date, channel and customer group.
     *
     * @return mixed
     */
    public function getCatalogRulePrice()
    {
        return $this->product->catalog_rule_prices
            ->where('customer_group_id', $this->customerGroup->id)
            ->where('channel_id', $this->channel->id)
            ->where('rule_date', Carbon::now()->format('Y-m-d'))
            ->first();
    }
}
