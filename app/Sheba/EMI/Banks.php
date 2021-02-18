<?php namespace Sheba\EMI;

class Banks
{


    public static function get($amount, $icons_folder = null)
    {
        $icons_folder = $icons_folder ?: getEmiBankIconsFolder(true);
        $banks = [
            [
                "name"     => "Midland Bank Ltd",
                "logo"     => $icons_folder . "midland_bank.png",
                "asset"    => "midland_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => null
                ]
            ],
            [
                "name"     => "SBAC Bank",
                "logo"     => $icons_folder . "sbac_bank.jpg",
                "asset"    => "sbac_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => null
                ]
            ],
            [
                "name"     => "Meghna Bank Limited",
                "logo"     => $icons_folder . "meghna_bank.png",
                "asset"    => "meghna_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => null
                ]
            ],
            [
                "name"     => "NRB Bank Limited",
                "logo"     => $icons_folder . "nrb_bank.png",
                "asset"    => "nrb_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => null
                ]
            ],
            [
                "name"     => "STANDARD CHARTERED BANK",
                "logo"     => $icons_folder . "standard_chartered.png",
                "asset"    => "standard_chartered",
                "emi(month : interest%)" => [
                    3 => 3.50,
                    6 => 5.5,
                    9 => 8,
                    12 => 10.5,
                    18 => 13.5,
                    24 => 17.5,
                    30 => null,
                    36 => 22.5
                ]
            ],
            [
                "name"     => "STANDARD BANK",
                "logo"     => $icons_folder . "standard_bank.png",
                "asset"    => "standard_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => null
                ]
            ],
            [
                "name"     => "SOUTHEAST BANK",
                "logo"     => $icons_folder . "sebl_bank.png",
                "asset"    => "sebl_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => 16.5,
                    36 => 19.5
                ]
            ],
            [
                "name"     => "NCC BANK",
                "logo"     => $icons_folder . "ncc_bank.png",
                "asset"    => "ncc_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => 19.5
                ]
            ],
            [
                "name"     => "MUTUAL TRUST BANK",
                "logo"     => $icons_folder . "mtb_bank.png",
                "asset"    => "mtb_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => 19.5
                ]
            ],
            [
                "name"     => "JAMUNA BANK",
                "logo"     => $icons_folder . "jamuna_bank.png",
                "asset"    => "jamuna_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => 19.5
                ]
            ],
            [
                "name"     => "EASTERN BANK",
                "logo"     => $icons_folder . "ebl.png",
                "asset"    => "midland_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => 16.5,
                    36 => 19.5
                ]
            ],
            [
                "name"     => "DUTCH BANGLA BANK",
                "logo"     => $icons_folder . "dbbl_bank.png",
                "asset"    => "dbbl_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => 19.5
                ]
            ],
            [
                "name"     => "DHAKA BANK LIMITED",
                "logo"     => $icons_folder . "dhaka_bank.png",
                "asset"    => "dhaka_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => null,
                    24 => null,
                    30 => null,
                    36 => null
                ]
            ],
            [
                "name"     => "CITY BANK LIMITED",
                "logo"     => $icons_folder . "city_bank.png",
                "asset"    => "city_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => 16.5,
                    36 => 19.5
                ]
            ],
            [
                "name"     => "BRAC BANK LIMITED",
                "logo"     => $icons_folder . "brac_bank.png",
                "asset"    => "brac_bank",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => null
                ]
            ],
            [
                "name"     => "BANK ASIA LIMITED",
                "logo"     => $icons_folder . "bank_asia.png",
                "asset"    => "bank_asia",
                "emi(month : interest%)" => [
                    3 => 3.50,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => null
                ]
            ],
            [
                "name"     => "LankaBangla Finance Limited",
                "logo"     => "",
                "asset"    => "",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => 19.5
                ]
            ],
            [
                "name"     => "NRB Commercial Bank",
                "logo"     => "",
                "asset"    => "",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => 11.5,
                    24 => 15.5,
                    30 => null,
                    36 => 19.5
                ]
            ],
            [
                "name"     => "Shahjalal Islami Bank Limited (SJIBL)",
                "logo"     => "",
                "asset"    => "",
                "emi(month : interest%)" => [
                    3 => 3,
                    6 => 4.5,
                    9 => 6.5,
                    12 => 8.5,
                    18 => null,
                    24 => null,
                    30 => null,
                    36 => null
                ]
            ],
        ];
        return $banks;
    }
}
