<?php

/**
 * score = (quality_score x (100-Wi)) + (impression_score x Wi)
 * To find best partners only, Wi = 0
 * Wi = weight of impression.
 */

return [
    'xtra_vendor_id' => env('XTRA_VENDOR_ID'),
    'vendor_promo_applicable_sales_channels' => ['Web', 'App', 'App-iOS'],
    'xtra_vendor_tag_id' =>  env('XTRA_VENDOR_TAG_ID'),
    'xtra_vendor_contribution_in_percentage' => 96,
    'xtra_promo_default_title' => 'Xtra voucher',
];
