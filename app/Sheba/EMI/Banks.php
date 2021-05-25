<?php namespace Sheba\EMI;

use Illuminate\Support\Collection;

class Banks
{
    private $calculator;
    private $amount;

    public function __construct()
    {
        $this->calculator = new Calculator();
    }

    public function get($icons_folder = null)
    {
        $icons_folder = $icons_folder ?: getEmiBankIconsFolder(true);
        $banks = collect([
            [
                "name"     => "Midland Bank Ltd",
                "logo"     => $icons_folder . "midland_bank.png",
                "asset"    => "midland_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5
                ]
            ],
            [
                "name"     => "SBAC Bank",
                "logo"     => $icons_folder . "sbac_bank.jpg",
                "asset"    => "sbac_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5
                ]
            ],
            [
                "name"     => "Meghna Bank Limited",
                "logo"     => $icons_folder . "meghna_bank.png",
                "asset"    => "meghna_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
                ]
            ],
            [
                "name"     => "NRB Bank Limited",
                "logo"     => $icons_folder . "nrb_bank.png",
                "asset"    => "nrb_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5
                ]
            ],
            [
                "name"     => "STANDARD CHARTERED BANK",
                "logo"     => $icons_folder . "standard_chartered.png",
                "asset"    => "standard_chartered",
                "emi" => [
                    3 => 3.50,
                    6 => 5.5,
                    9 => 8,
                    12 => 10.5,
//                    18 => 13.5,
//                    24 => 17.5,
//                    36 => 22.5
                ]
            ],
            [
                "name"     => "STANDARD BANK",
                "logo"     => $icons_folder . "standard_bank.png",
                "asset"    => "standard_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5
                ]
            ],
            [
                "name"     => "SOUTHEAST BANK",
                "logo"     => $icons_folder . "sebl_bank.png",
                "asset"    => "sebl_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
//                    30 => 16.5,
//                    36 => 19.5
                ]
            ],
            [
                "name"     => "NCC BANK",
                "logo"     => $icons_folder . "ncc_bank.png",
                "asset"    => "ncc_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
//                    36 => 19.5
                ]
            ],
            [
                "name"     => "MUTUAL TRUST BANK",
                "logo"     => $icons_folder . "mtb_bank.png",
                "asset"    => "mtb_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
//                    36 => 19.5
                ]
            ],
            [
                "name"     => "JAMUNA BANK",
                "logo"     => $icons_folder . "jamuna_bank.png",
                "asset"    => "jamuna_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
//                    36 => 19.5
                ]
            ],
            [
                "name"     => "EASTERN BANK",
                "logo"     => $icons_folder . "ebl.png",
                "asset"    => "midland_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
//                    30 => 16.5,
//                    36 => 19.5
                ]
            ],
            [
                "name"     => "DUTCH BANGLA BANK",
                "logo"     => $icons_folder . "dbbl_bank.png",
                "asset"    => "dbbl_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
//                    36 => 19.5
                ]
            ],
            [
                "name"     => "DHAKA BANK LIMITED",
                "logo"     => $icons_folder . "dhaka_bank.png",
                "asset"    => "dhaka_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5
                ]
            ],
            [
                "name"     => "CITY BANK LIMITED",
                "logo"     => $icons_folder . "city_bank.png",
                "asset"    => "city_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
//                    30 => 16.5,
//                    36 => 19.5
                ]
            ],
            [
                "name"     => "BRAC BANK LIMITED",
                "logo"     => $icons_folder . "brac_bank.png",
                "asset"    => "brac_bank",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5
                ]
            ],
            [
                "name"     => "BANK ASIA LIMITED",
                "logo"     => $icons_folder . "bank_asia.png",
                "asset"    => "bank_asia",
                "emi" => [
                    3 => 3.50,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5
                ]
            ],
            [
                "name"     => "LankaBangla Finance Limited",
                "logo"     => "https://cdn-shebadev.s3.ap-south-1.amazonaws.com/images/emi_bank_icon/lanka_bangla_bank.png",
                "asset"    => "bank_asia",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
//                    36 => 19.5
                ]
            ],
            [
                "name"     => "NRB Commercial Bank",
                "logo"     => "https://cdn-shebadev.s3.ap-south-1.amazonaws.com/images/emi_bank_icon/nrbc_bank.png",
                "asset"    => "bank_asia",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
//                    18 => 11.5,
//                    24 => 15.5,
//                    36 => 19.5
                ]
            ],
            [
                "name"     => "Shahjalal Islami Bank Limited (SJIBL)",
                "logo"     => "https://cdn-shebadev.s3.ap-south-1.amazonaws.com/images/emi_bank_icon/shahjala_bank.png",
                "asset"    => "bank_asia",
                "emi" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5
                ]
            ],
        ]);

        $banks = $this->formatEmi($banks);

        return $banks->values();
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function formatEmi(Collection $banks)
    {
        return $banks->map(function ($bank) {
            $emis = collect();
            foreach ($bank['emi'] as $key => $value) {
                $emis->push($this->calculator->calculateMonthWiseCharge($this->amount, $key, $value));
            }
            $emis = $emis->sortBy('interest_value');
            $bank['emi'] = $emis;
            $bank['lowest_emi'] = $emis[0]['interest_value'];
            return $bank;
        })->sortBy('lowest_emi')->map(function ($bank) {
            unset($bank['lowest_emi']);
            $bank['emi'] = $bank['emi']->forgetEach('interest_value')->values();
            return $bank;
        });
    }
}
