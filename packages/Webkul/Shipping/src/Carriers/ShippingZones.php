<?php

namespace Webkul\Shipping\Carriers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartShippingRate;
use Webkul\Shipping\Models\ShippingZoneMethod;
use Webkul\Shipping\Repositories\ShippingZoneRepository;

class ShippingZones extends AbstractShipping
{
    /**
     * Shipping method carrier code.
     *
     * @var string
     */
    protected $code = 'shippingzones';

    /**
     * Calculate rates for shipping zones.
     *
     * @return array|false
     */
    public function calculate()
    {
        if (! $this->getConfigData('active')) {
            return false;
        }

        $cart = Cart::getCart();

        if (! $cart) {
            return false;
        }

        $shippingAddress = $cart->shipping_address;

        if (! $shippingAddress) {
            return false;
        }

        $zone = app(ShippingZoneRepository::class)->findMatchingZone(
            $shippingAddress->country,
            $shippingAddress->postcode
        );

        if (! $zone) {
            return false;
        }

        $rates = [];

        foreach ($zone->methods as $method) {
            if (! $method->status) {
                continue;
            }

            $rates[] = $this->getRate($zone, $method, $cart);
        }

        return $rates ?: false;
    }

    /**
     * Build the cart shipping rate for a single zone method.
     */
    protected function getRate($zone, ShippingZoneMethod $method, $cart): CartShippingRate
    {
        $basePrice = $this->getMethodBasePrice($method, $cart);

        $cartShippingRate = new CartShippingRate;

        $cartShippingRate->carrier = $this->getCode();
        $cartShippingRate->carrier_title = $zone->name;
        $cartShippingRate->method = $this->getCode().'_'.$method->id;
        $cartShippingRate->method_title = $method->title;
        $cartShippingRate->method_description = $zone->name;
        $cartShippingRate->price = core()->convertPrice($basePrice);
        $cartShippingRate->base_price = $basePrice;

        return $cartShippingRate;
    }

    /**
     * Compute the base (store currency) price for a zone method.
     *
     * @return float
     */
    protected function getMethodBasePrice(ShippingZoneMethod $method, $cart): float
    {
        if ($method->type === ShippingZoneMethod::FREE_SHIPPING) {
            return 0;
        }

        $rate = (float) $method->rate;

        if ($method->calculation_type !== 'per_unit') {
            return $rate;
        }

        $total = 0;

        foreach ($cart->items as $item) {
            if ($item->getTypeInstance()->isStockable()) {
                $total += $rate * $item->quantity;
            }
        }

        return $total;
    }
}
