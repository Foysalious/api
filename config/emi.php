<?php
$breakdowns = [
    ['month' => 3, 'interest' => 3.0],
    ['month' => 6, 'interest' => 4.50],
    ['month' => 9, 'interest' => 6.50],
    ['month' => 12, 'interest' => 8.50],
    ['month' => 18, 'interest' => 11.50]
];
return [
    /*
    |--------------------------------------------------------------------------
    | Minimum Emi Amount
    |--------------------------------------------------------------------------
    |
    */

    'minimum_emi_amount' => env('MINIMUM_EMI_AMOUNT', 15000),

    /*
    |--------------------------------------------------------------------------
    | Bank Transaction Fee Percentage
    |--------------------------------------------------------------------------
    |
    */

    'bank_fee_percentage' => env('BANK_FEE_PERCENTAGE', 2.5),

    'valid_months' => [3, 6, 9, 12, 18],

    /**
     * EMI CONFIGURATION FOR MANAGER
     */
    'manager'      => [
        'minimum_emi_amount'  => env('MANAGER_MINIMUM_EMI_AMOUNT', 15000),
        'bank_fee_percentage' => env('MANAGER_BANK_FEE_PERCENTAGE', 2.5),
        'breakdowns'          => $breakdowns,
        'valid_months'        => array_map(function ($item) { return $item['month']; }, $breakdowns)
    ]
];
