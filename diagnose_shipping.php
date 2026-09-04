<?php

/**
 * Temporary diagnostic script - replicates the "Estimate Shipping" request,
 * including selecting a shipping method (clicking a rate radio button),
 * and prints the resulting cart totals plus any error with a full stack
 * trace, so we can see exactly why Delivery Charges/Tax stay at 0.00
 * after selecting a rate.
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
use Webkul\Checkout\Models\CartAddress;
use Webkul\Shipping\Facades\Shipping;

function attachAddress($cart, $address)
{
    $cart->setRelation('billing_address', $address);
    $cart->setRelation('shipping_address', $address);
}

echo "=== Loading a cart to test with ===\n";

$cartModel = \Webkul\Checkout\Models\Cart::query()
    ->where('is_active', 1)
    ->orderByDesc('updated_at')
    ->first();

if (! $cartModel) {
    echo "No active cart found in the database.\n";
    exit(1);
}

echo 'Using cart #'.$cartModel->id."\n\n";

echo "=== STEP 1: estimate for Israel (like changing the Country dropdown) ===\n";

$address = (new CartAddress)->fill([
    'country' => 'IL',
    'state' => 'Dhaka',
    'postcode' => '8560',
    'cart_id' => $cartModel->id,
]);

attachAddress($cartModel, $address);

Cart::setCart($cartModel);

Cart::collectTotals();

$cart = Cart::getCart();

attachAddress($cart, $address);

$rates = Shipping::collectRates();

$methodCode = null;

foreach ($rates['shippingMethods'] as $carrierGroup) {
    foreach ($carrierGroup['rates'] as $rate) {
        echo 'Found rate: method='.$rate->method.' price='.$rate->price."\n";
        $methodCode = $rate->method;
    }
}

if (! $methodCode) {
    echo "No rate found for Israel - stopping here.\n";
    exit(1);
}

echo "\ncart->shipping_amount after estimate only (no method selected): ".Cart::getCart()->shipping_amount."\n";
echo "cart->tax_total after estimate only: ".Cart::getCart()->tax_total."\n";

echo "\n=== STEP 2: select that rate (like clicking its radio button) ===\n";

$address2 = (new CartAddress)->fill([
    'country' => 'IL',
    'state' => 'Dhaka',
    'postcode' => '8560',
    'cart_id' => $cartModel->id,
]);

$cart = Cart::getCart();
attachAddress($cart, $address2);
Cart::setCart($cart);

echo 'Selecting method code: '.$methodCode."\n";

$saved = Cart::saveShippingMethod($methodCode);
echo 'saveShippingMethod() returned: ';
var_dump($saved);

echo 'cart->shipping_method after save: '.var_export(Cart::getCart()->shipping_method, true)."\n";

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
attachAddress($cart, $address2);

echo "\ncart->shipping_method: ".var_export($cart->shipping_method, true)."\n";

$selectedRate = $cart->selected_shipping_rate;
echo 'cart->selected_shipping_rate found? ';
var_dump(! is_null($selectedRate));

if ($selectedRate) {
    echo 'selected_shipping_rate->price: '.$selectedRate->price."\n";
}

echo "\ncart->shipping_amount: ".$cart->shipping_amount."\n";
echo 'cart->base_shipping_amount: '.$cart->base_shipping_amount."\n";
echo 'cart->tax_total: '.$cart->tax_total."\n";
echo 'cart->grand_total: '.$cart->grand_total."\n";

echo "\nAll cart_shipping_rates rows for this cart right now:\n";
foreach (\Webkul\Checkout\Models\CartShippingRate::where('cart_id', $cartModel->id)->get() as $r) {
    echo '  id='.$r->id.' method='.$r->method.' price='.$r->price."\n";
}
