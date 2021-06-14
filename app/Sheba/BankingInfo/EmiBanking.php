<?php namespace App\Sheba\BankingInfo;

use Sheba\Helpers\ConstGetter;

class EmiBanking
{
    use ConstGetter;

    const MIDLAND_BANK = 'midland_bank';
    const JAMUNA_BANK = 'jamuna_bank';
    const EASTERN_BANK = 'eastern_bank';
    const DUTCH_BANGLA_BANK = 'dutch_bangla_bank';
    const MUTUAL_TRUST_BANK = 'mutual_trust_bank';
    const BANK_ASIA = 'bank_asia';
    const MEGHNA_BANK = 'meghna_bank';
    const BRAC_BANK = 'brac_bank';
    const STANDARD_BANK_LIMITED = 'standard_bank_limited';
    const NRBC_BANK = 'nrbc_bank';
    const SOUTH_BANGLA_AGRICULTURE_AND_COMMERCE_BANK_LIMITED = 'south_bangla_agriculture_and_commerce_bank_limited';
    const STANDARD_CHARTERED_BANK = 'standard_chartered_bank';
    const SHAHJALAL_ISLAMI_BANK = 'shahjalal_islami_bank';
    const SOUTHEAST_BANK = 'southeast_bank';
    const DHAKA_BANK = 'dhaka_bank';
    const NATIONAL_CREDIT_AND_COMMERCE_BANK_LIMITED = 'national_credit_and_commerce_bank_limited';
    const LANKABANGLA_BANK = 'lankabangla_bank';
    const NRB_BANK = 'nrb_bank';
    const CITY_BANK = 'city_bank';
    const UNITED_COMMERCIAL_BANK = 'united_commercial_bank';


    public static function getPublishedBank()
    {
        return [
            self::MIDLAND_BANK,
            self::JAMUNA_BANK,
            self::EASTERN_BANK,
            self::DUTCH_BANGLA_BANK,
            self::MUTUAL_TRUST_BANK,
            self::BANK_ASIA,
            self::MEGHNA_BANK,
            self::BRAC_BANK,
            self::STANDARD_BANK_LIMITED,
            self::NRBC_BANK,
            self::SOUTH_BANGLA_AGRICULTURE_AND_COMMERCE_BANK_LIMITED,
            self::STANDARD_CHARTERED_BANK,
            self::SHAHJALAL_ISLAMI_BANK,
            self::SOUTHEAST_BANK,
            self::DHAKA_BANK,
            self::NATIONAL_CREDIT_AND_COMMERCE_BANK_LIMITED,
            self::LANKABANGLA_BANK,
            self::NRB_BANK,
            self::CITY_BANK,
            self::UNITED_COMMERCIAL_BANK
        ];
    }
}
