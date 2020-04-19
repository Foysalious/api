<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Minimum Emi Amount
    |--------------------------------------------------------------------------
    |
    */

    'minimum_emi_amount' => env('MINIMUM_EMI_AMOUNT',15000),

    /*
    |--------------------------------------------------------------------------
    | Bank Transaction Fee Percentage
    |--------------------------------------------------------------------------
    |
    */

    'bank_fee_percentage' => env('BANK_FEE_PERCENTAGE',2.5),

];