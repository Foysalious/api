<?php

use Sheba\Dal\PartnerBankLoan\LoanTypes;

return [
    'old_app_version'                      => env('LOAN_OLD_APP_VERSION', 211503),
    'fee'                                  => [
        LoanTypes::TERM  => env('TERM_LOAN_FEE', 10),
        LoanTypes::MICRO => env('MICRO_LOAN_FEE', 50)
    ],
    'minimum_day'                          => [
        LoanTypes::TERM  => env('TERM_LOAN_MINIMUM_DAY', 15),
        LoanTypes::MICRO => env('MICRO_LOAN_MINIMUM_DAY', 7)
    ],
    'minimum_amount'                       => [
        LoanTypes::TERM  => env('TERM_LOAN_MINIMUM_AMOUNT', 5000),
        LoanTypes::MICRO => env('MICRO_LOAN_MINIMUM_AMOUNT', 10000)
    ],

    'maximum_amount'                       => [
        LoanTypes::TERM  => env('TERM_LOAN_MAXIMUM_AMOUNT', 50000),
        LoanTypes::MICRO => env('MICRO_LOAN_MAXIMUM_AMOUNT', 50000)
    ],
    'repayment_defaulter_default_duration' => 7,
    'micro_loan_claim_transaction_fee'     => 10,
    'micro_loan_assigned_bank_id'          => env('MICRO_LOAN_ASSIGNED_BANK', 1),
    'micro_loan_annual_fee'                 => 100,
    'minimum_repayment_amount'  => 1000,
    'defaulter_fine' => .75
];
