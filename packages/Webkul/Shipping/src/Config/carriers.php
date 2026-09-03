<?php

return [
    'flatrate' => [
        'code' => 'flatrate',
        'title' => 'Flat Rate',
        'description' => 'Flat Rate Shipping',
        'active' => true,
        'default_rate' => '10',
        'type' => 'per_unit',
        'class' => 'Webkul\Shipping\Carriers\FlatRate',
    ],

    'free' => [
        'code' => 'free',
        'title' => 'Free Shipping',
        'description' => 'Free Shipping',
        'active' => true,
        'default_rate' => '0',
        'class' => 'Webkul\Shipping\Carriers\Free',
    ],

    'shippingzones' => [
        'code' => 'shippingzones',
        'title' => 'Shipping Zones',
        'description' => 'Shipping Zones',
        'active' => true,
        'class' => 'Webkul\Shipping\Carriers\ShippingZones',
    ],
];
