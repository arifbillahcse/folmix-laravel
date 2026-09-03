<?php

/**
 * Temporary diagnostic script - replicates the "Estimate Shipping" request
 * against a real cart from the database and prints any error with a full
 * stack trace, so we can see exactly why no shipping rate is returned.
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

echo "=== Loading a cart to test with ===\n";

$cartModel = \Webkul\Checkout\Models\Cart::query()
    ->where('is_active', 1)
    ->orderByDesc('updated_at')
    ->first();

if (! $cartModel) {
    echo "No active cart found in the database. Add an item to a cart on the site first, then re-run this script.\n";
    exit(1);
}

echo 'Using cart #'.$cartModel->id." (items: {$cartModel->items()->count()}, base_sub_total: {$cartModel->base_sub_total})\n\n";

echo "=== Building a temporary Italy/rome/0100 address ===\n";

$address = (new CartAddress)->fill([
    'country' => 'IT',
    'state' => 'rome',
    'postcode' => '0100',
    'cart_id' => $cartModel->id,
]);

$cartModel->setRelation('billing_address', $address);
$cartModel->setRelation('shipping_address', $address);

Cart::setCart($cartModel);

echo "Address attached. cart->shipping_address->country = ".Cart::getCart()->shipping_address->country."\n\n";

echo "=== Calling Cart::collectTotals() ===\n";

try {
    Cart::collectTotals();
    echo "collectTotals() succeeded.\n";
} catch (\Throwable $e) {
    echo "collectTotals() THREW AN EXCEPTION:\n";
    echo get_class($e).': '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}

echo "\n=== Calling Shipping::collectRates() directly ===\n";

try {
    $result = Shipping::collectRates();
    echo "collectRates() returned:\n";
    print_r($result);
} catch (\Throwable $e) {
    echo "collectRates() THREW AN EXCEPTION:\n";
    echo get_class($e).': '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}

echo "\n=== Calling the shippingzones carrier's calculate() directly ===\n";

try {
    $carrier = new \Webkul\Shipping\Carriers\ShippingZones;
    $rates = $carrier->calculate();
    echo "calculate() returned:\n";
    var_dump($rates);
} catch (\Throwable $e) {
    echo "calculate() THREW AN EXCEPTION:\n";
    echo get_class($e).': '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}

echo "\n=== Step-by-step trace of what calculate() sees internally ===\n";

$activeFlag = core()->getConfigData('sales.carriers.shippingzones.active');
echo 'active flag: ';
var_dump($activeFlag);

$traceCart = Cart::getCart();
echo 'Cart::getCart() is null? ';
var_dump(is_null($traceCart));

$traceAddress = $traceCart?->shipping_address;
echo 'shipping_address is null? ';
var_dump(is_null($traceAddress));

if ($traceAddress) {
    echo 'shipping_address->country: '.var_export($traceAddress->country, true)."\n";
    echo 'shipping_address->postcode: '.var_export($traceAddress->postcode, true)."\n";
}

$traceZone = app(\Webkul\Shipping\Repositories\ShippingZoneRepository::class)->findMatchingZone(
    $traceAddress?->country,
    $traceAddress?->postcode
);
echo 'findMatchingZone() result: ';
var_dump($traceZone ? $traceZone->id.' - '.$traceZone->name : null);

if ($traceZone) {
    echo 'zone methods count: '.$traceZone->methods->count()."\n";
    foreach ($traceZone->methods as $m) {
        echo '  method #'.$m->id.' status='.var_export($m->status, true).' title='.$m->title."\n";
    }
}
