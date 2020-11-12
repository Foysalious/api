<?php

use Sheba\Dal\PartnerBankLoan\LoanTypes;

return [
    'old_app_version'                      => env('LOAN_OLD_APP_VERSION', 211503),
    'fee'                                  => [
        LoanTypes::TERM  => env('TERM_LOAN_FEE', 200),
        LoanTypes::MICRO => env('MICRO_LOAN_FEE', 50)
    ],
    'minimum_day'                          => [
        LoanTypes::TERM  => env('TERM_LOAN_MINIMUM_DAY', 15),
        LoanTypes::MICRO => env('MICRO_LOAN_MINIMUM_DAY', 5)
    ],
    'minimum_amount'                       => [
        LoanTypes::TERM  => env('TERM_LOAN_MINIMUM_AMOUNT', 5000),
        LoanTypes::MICRO => env('MICRO_LOAN_MINIMUM_AMOUNT', 1000)
    ],

    'maximum_amount'                       => [
        LoanTypes::TERM  => env('TERM_LOAN_MAXIMUM_AMOUNT', 5000000),
        LoanTypes::MICRO => env('MICRO_LOAN_MAXIMUM_AMOUNT', 5000)
    ],
    'repayment_defaulter_default_duration' => 6,
    'micro_loan_claim_transaction_fee'     => 10,
    'micro_loan_assigned_bank_id'          => env('MICRO_LOAN_ASSIGNED_BANK', 1),
    'micro_loan_annual_fee'                 => 100,
    'minimum_repayment_amount'  => 10,
    'defaulter_fine' => .75,
    'micro_loan_sheba_interest' => .057
];
