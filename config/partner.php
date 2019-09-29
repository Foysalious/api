<?php

return [
    'subscription_featured_package_id' => explode(',', env('PARTNER_SUBSCRIPTION_FEATURED_PACKAGE_ID', 3)),
    'subscription_billing_type' => [
        'monthly' => 'monthly',
        'half_yearly' => 'half_yearly',
        'yearly' => 'yearly'
    ]
];