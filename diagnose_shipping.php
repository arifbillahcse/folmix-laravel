<?php

/**
 * Temporary diagnostic script - replicates the real checkout Address-step
 * flow (OnepageController::storeAddress) to see why changing the country
 * on the checkout page still shows the old Italy shipping rate/tax
 * instead of recalculating for the new country.
 *
 * Run from the project root:
 *   php diagnose_shipping.php
 *
 * Delete this file once the issue is found - it is not meant to stay in
 * the codebase.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Webkul\Checkout\Facades\Cart;
use Webkul\Shipping\Facades\Shipping;

echo "=== Loading a cart to test with ===\n";

$cartModel = \Webkul\Checkout\Models\Cart::query()
    ->where('is_active', 1)
    ->orderByDesc('updated_at')
    ->first();

if (! $cartModel) {
    echo "No active cart found in the database.\n";
    exit(1);
}

echo 'Using cart #'.$cartModel->id."\n";
echo 'Current cart.shipping_method (before): '.var_export($cartModel->shipping_method, true)."\n\n";

Cart::setCart($cartModel);

echo "=== Simulating storeAddress() with a Bangladesh address ===\n";

$addressParams = [
    'first_name' => 'MD.',
    'last_name' => 'Arif',
    'email' => 'test@example.com',
    'address' => ['H-161, Oabda Sorok'],
    'country' => 'BD',
    'state' => 'Barisal',
    'city' => 'Barisal',
    'postcode' => '8560',
    'phone' => '01779440297',
];

// Same shape OnepageController::storeAddress() builds via CartAddressRequest.
$params = [
    'billing' => $addressParams,
    'shipping' => $addressParams,
];

Cart::saveAddresses($params);

$cart = Cart::getCart();

echo 'cart.shipping_method (right after saveAddresses, should be null): '.var_export($cart->shipping_method, true)."\n";

echo "\nRemaining cart_shipping_rates rows (should be 0): ".
    \Webkul\Checkout\Models\CartShippingRate::where('cart_id', $cartModel->id)->count()."\n";

echo "\n=== Calling Cart::collectTotals() (as storeAddress() does) ===\n";

try {
    Cart::collectTotals();
    echo "collectTotals() succeeded.\n";
} catch (\Throwable $e) {
    echo "collectTotals() THREW AN EXCEPTION:\n";
    echo get_class($e).': '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}

$cart = Cart::getCart();

echo "\ncart.shipping_address->country: ".var_export($cart->shipping_address?->country, true)."\n";
echo 'cart.shipping_amount: '.$cart->shipping_amount."\n";
echo 'cart.tax_total: '.$cart->tax_total."\n";
echo 'cart.grand_total: '.$cart->grand_total."\n";

echo "\n=== Calling Shipping::collectRates() (as storeAddress() does next) ===\n";

try {
    $rates = Shipping::collectRates();
    echo "collectRates() returned:\n";
    foreach ($rates['shippingMethods'] as $carrier => $group) {
        foreach ($group['rates'] as $rate) {
            echo '  carrier='.$carrier.' method='.$rate->method.' title='.$rate->method_title.' price='.$rate->price."\n";
        }
    }
} catch (\Throwable $e) {
    echo "collectRates() THREW AN EXCEPTION:\n";
    echo get_class($e).': '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}

echo "\n=== Final cart state after the full storeAddress() sequence ===\n";

$cart = Cart::getCart();
echo 'cart.shipping_method: '.var_export($cart->shipping_method, true)."\n";
echo 'cart.shipping_amount: '.$cart->shipping_amount."\n";
echo 'cart.tax_total: '.$cart->tax_total."\n";
echo 'cart.grand_total: '.$cart->grand_total."\n";

echo "\n=== What the checkout summary endpoint would return right now ===\n";

$summaryResource = new \Webkul\Shop\Http\Resources\CartResource(Cart::getCart());
$serialized = $summaryResource->jsonSerialize();
echo 'formatted_shipping_amount: '.($serialized['formatted_shipping_amount'] ?? 'N/A')."\n";
echo 'formatted_tax_total: '.($serialized['formatted_tax_total'] ?? 'N/A')."\n";
echo 'formatted_grand_total: '.($serialized['formatted_grand_total'] ?? 'N/A')."\n";
