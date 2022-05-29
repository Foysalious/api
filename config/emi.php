<?php

$breakdowns = [
    ['month' => 3, 'interest' => 0.0],
    ['month' => 6, 'interest' => 0.00],
    ['month' => 9, 'interest' => 0.00],
    ['month' => 12, 'interest' => 0.00],
    ['month' => 18, 'interest' => 00.00]
];

return [
    'minimum_emi_amount' => (double)env('MINIMUM_EMI_AMOUNT', 5000),
    'bank_fee_percentage' => (double)env('BANK_FEE_PERCENTAGE', 2.5),
    'valid_months' => [3, 6, 9, 12, 18],
    'breakdowns' => [
        ['month' => 3, 'interest' => 3.0],
        ['month' => 6, 'interest' => 4.50],
        ['month' => 9, 'interest' => 6.50],
        ['month' => 12, 'interest' => 8.50]
    ],

    /**
     * EMI CONFIGURATION FOR MANAGER
     */
    'manager' => [
        'minimum_emi_amount' => (double)env('MANAGER_MINIMUM_EMI_AMOUNT', 5000),
        'bank_fee_percentage' => (double)env('MANAGER_BANK_FEE_PERCENTAGE', 2),
        'breakdowns' => $breakdowns,
        'valid_months' => array_map(function ($item) {
            return $item['month'];
        }, $breakdowns)
    ]
];
