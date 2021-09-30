<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Compliance related constants
    |--------------------------------------------------------------------------
    |
    |
    */

    "first_transaction_limit"  => 100000,
    "second_transaction_limit" => 500000,
    "third_transaction_limit"  => 1000000,

    "first_limit_required_fields" => [
        'bank_account', 'shop_type', 'monthly_transaction_volume', 'registration_year', 'email', 'trade_license',
        'tin_no', 'tin_licence_photo', 'trade_license'
    ],

    "second_limit_required_fields" => [
        'bank_account', 'shop_type', 'monthly_transaction_volume', 'registration_year', 'email', 'trade_license',
        'tin_no', 'tin_licence_photo', 'trade_license', 'cpv_status', 'grantor', 'security_cheque'
    ],

    "third_limit_required_fields" => [
        'bank_account', 'shop_type', 'monthly_transaction_volume', 'registration_year', 'email', 'trade_license',
        'tin_no', 'tin_licence_photo', 'trade_license', 'cpv_status', 'grantor', 'security_cheque', 'electricity_bill_image'
    ],

];
