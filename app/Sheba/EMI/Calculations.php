<?php


namespace Sheba\EMI;


class Calculations {
    public static function calculateEmiCharges($amount) {
        $breakdowns = self::breakdowns();
        $emi        = [];
        foreach ($breakdowns as $item) {
            array_push($emi, self::calculateMonthWiseCharge($amount, $item['month'], $item['interest']));
        }
        return $emi;
    }

    public static function calculateMonthWiseCharge($amount, $month, $interest, $format = true) {
        $rate                 = ($interest / 100);
        $bank_transaction_fee = self::getBankTransactionFee($amount);
        return $format ? [
            "number_of_months"     => $month,
            "interest"             => "$interest%",
            "total_interest"       => number_format(ceil(($amount * $rate))),
            "bank_transaction_fee" => number_format($bank_transaction_fee),
            "amount"               => number_format(ceil((($amount + ($amount * $rate)) + $bank_transaction_fee) / $month)),
            "total_amount"         => number_format(($amount + ceil(($amount * $rate))) + $bank_transaction_fee)
        ] : [
            "number_of_months"     => $month,
            "interest"             => $interest,
            "total_interest"       => ceil(($amount * $rate)),
            "bank_transaction_fee" => $bank_transaction_fee,
            "amount"               => ceil((($amount + ($amount * $rate)) + $bank_transaction_fee) / $month),
            "total_amount"         => ($amount + ceil(($amount * $rate))) + $bank_transaction_fee
        ];
    }

    public static function breakdowns() {
        return config('emi.manager.breakdowns');
    }

    public static function getBankTransactionFee($amount) {
        return ceil($amount * (config('emi.manager.bank_fee_percentage') / 100));
    }

    public static function getMonthData($amount, $month,$format=true) {

        $data = self::getMonthInterest($month);
        if (!empty($data)) {
            return self::calculateMonthWiseCharge($amount, $data['month'], $data['interest'],$format);
        } else {
            return [];
        }
    }

    public static function getMonthInterest($month) {
        $breakdowns = self::breakdowns();
        $data       = array_values(array_filter($breakdowns, function ($item) use ($month) {
            return $item['month'] == $month;
        }));
        return !empty($data) ? $data[0] : [];
    }

    public static function BankDetails($icons_folder) {
        return [
            [
                "name"  => "Midland Bank Ltd",
                "logo"  => $icons_folder . "midland_bank.png",
                "asset" => "midland_bank"
            ],
            [
                "name"  => "SBAC Bank",
                "logo"  => $icons_folder . "sbac_bank.jpg",
                "asset" => "sbac_bank"
            ],
            [
                "name"  => "Meghna Bank Limited",
                "logo"  => $icons_folder . "meghna_bank.png",
                "asset" => "meghna_bank"
            ],
            [
                "name"  => "NRB Bank Limited",
                "logo"  => $icons_folder . "nrb_bank.png",
                "asset" => "nrb_bank"
            ],
            [
                "name"  => "STANDARD CHARTERED BANK",
                "logo"  => $icons_folder . "standard_chartered.png",
                "asset" => "standard_chartered"
            ],
            [
                "name"  => "STANDARD BANK",
                "logo"  => $icons_folder . "standard_bank.png",
                "asset" => "standard_bank"
            ],
            [
                "name"  => "SOUTHEAST BANK",
                "logo"  => $icons_folder . "sebl_bank.png",
                "asset" => "sebl_bank"
            ],
            [
                "name"  => "NCC BANK",
                "logo"  => $icons_folder . "ncc_bank.png",
                "asset" => "ncc_bank"
            ],
            [
                "name"  => "MUTUAL TRUST BANK",
                "logo"  => $icons_folder . "mtb_bank.png",
                "asset" => "mtb_bank"
            ],
            [
                "name"  => "JAMUNA BANK",
                "logo"  => $icons_folder . "jamuna_bank.png",
                "asset" => "jamuna_bank"
            ],
            [
                "name"  => "EASTERN BANK",
                "logo"  => $icons_folder . "ebl.png",
                "asset" => "ebl"
            ],
            [
                "name"  => "DUTCH BANGLA BANK",
                "logo"  => $icons_folder . "dbbl_bank.png",
                "asset" => "dbbl_bank"
            ],
            [
                "name"  => "DHAKA BANK LIMITED",
                "logo"  => $icons_folder . "dhaka_bank.png",
                "asset" => "dhaka_bank"
            ],
            [
                "name"  => "CITY BANK LIMITED",
                "logo"  => $icons_folder . "city_bank.png",
                "asset" => "city_bank"
            ],
            [
                "name"  => "BRAC BANK LIMITED",
                "logo"  => $icons_folder . "brac_bank.png",
                "asset" => "brac_bank"
            ],
            [
                "name"  => "BANK ASIA LIMITED",
                "logo"  => $icons_folder . "bank_asia.png",
                "asset" => "bank_asia"
            ],
            //                [
            //                    "name" => "United Commercial Bank Ltd",
            //                    "logo" => $icons_folder."ucb.png",
            //                    "asset" => "ucb"
            //                ]
        ];
    }
}
