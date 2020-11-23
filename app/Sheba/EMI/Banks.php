<?php namespace Sheba\EMI;


class Banks
{
    public static function get($icons_folder = null)
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
