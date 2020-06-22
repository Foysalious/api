<?php namespace Sheba\EMI;


class Calculations
{
    public static function calculateEmiCharges($amount)
    {
        return self::_calculate($amount, config('emi.breakdowns'), self::getBankTransactionFee($amount));
    }

    public static function calculateEmiChargesForManager($amount)
    {
        return self::_calculate($amount, self::breakdownsForManager(), self::getBankTransactionFeeForManager($amount));
    }

    private static function _calculate($amount, $breakdowns, $bank_trx_fee)
    {
        $emi        = [];
        foreach ($breakdowns as $item) {
            array_push($emi, self::calculateMonthWiseCharge($amount, $item['month'], $item['interest'], $bank_trx_fee));
        }
        return $emi;
    }

    public static function calculateMonthWiseCharge($amount, $month, $interest, $bank_trx_fee = null, $format = true)
    {
        $rate                 = ($interest / 100);
        return $format ? [
            "number_of_months"     => $month,
            "interest"             => "$interest%",
            "total_interest"       => number_format(ceil(($amount * $rate))),
            "bank_transaction_fee" => number_format($bank_trx_fee),
            "amount"               => number_format(ceil((($amount + ($amount * $rate)) + $bank_trx_fee) / $month)),
            "total_amount"         => number_format(($amount + ceil(($amount * $rate))) + $bank_trx_fee)
        ] : [
            "number_of_months"     => $month,
            "interest"             => $interest,
            "total_interest"       => ceil(($amount * $rate)),
            "bank_transaction_fee" => $bank_trx_fee,
            "amount"               => ceil((($amount + ($amount * $rate)) + $bank_trx_fee) / $month),
            "total_amount"         => ($amount + ceil(($amount * $rate))) + $bank_trx_fee
        ];
    }

    public static function breakdownsForManager()
    {
        return config('emi.manager.breakdowns');
    }

    private static function _getBankTransactionFee($amount, $percentage)
    {
        return ceil($amount * ($percentage / 100));
    }

    public static function getBankTransactionFee($amount)
    {
        return self::_getBankTransactionFee($amount, config('emi.bank_fee_percentage'));
    }

    public static function getBankTransactionFeeForManager($amount)
    {
        return self::_getBankTransactionFee($amount, config('emi.manager.bank_fee_percentage'));
    }

    public static function getMonthData($amount, $month, $format=true)
    {
        $data = self::getMonthInterest($month);

        $bank_trx_fee = self::getBankTransactionFeeForManager($amount);

        return empty($data) ? [] : self::calculateMonthWiseCharge($amount, $data['month'], $data['interest'], $bank_trx_fee, $format);
    }

    public static function getMonthInterest($month)
    {
        $breakdowns = self::breakdownsForManager();
        $data       = array_values(array_filter($breakdowns, function ($item) use ($month) {
            return $item['month'] == $month;
        }));
        return !empty($data) ? $data[0] : [];
    }

    public static function getBankDetails($icons_folder = null)
    {
        $icons_folder = $icons_folder ?: getEmiBankIconsFolder(true);
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
            ]
        ];
    }
}
