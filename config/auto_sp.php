<?php

/**
 * score = (quality_score x (100-Wi)) + (impression_score x Wi)
 * To find best partners only, Wi = 0
 * Wi = weight of impression.
 */

return [
    'weights' => [
        'impression' => 50,
        'quality' => [
            'ita' => 15,
            'spro_app_usage' => 20,
            'ota' => 25,
            'complain' => 10,
            'max_revenue' => 5,
            'package' => 5,
            'avg_rating' => 10,
            'commission' => 10
        ]
    ],
    'new_customer_order_count' => 2
];
