<?php namespace App\Sheba\BankingInfo;

use Sheba\Helpers\ConstGetter;

class GeneralBanking
{
    use ConstGetter;

    const CITY_BANK = 'city_bank';
    const AB_BANK= 'ab_bank';
    const BANK_ASIA = 'bank_asia';
    const BRAC_BANK = 'brac_bank';
    const DHAKA_BANK = 'dhaka_bank';
    const DUTCH_BANGLA_BANK = 'dutch_bangla_bank';
    const EASTERN_BANK = 'eastern_bank';
    const IFIC_BANK = 'ific_bank';
    const JAMUNA_BANK = 'jamuna_bank';
    const meghna_bank = 'meghna_bank';
    const SHONALI_BANK = 'shonali_bank';
    const MODHUMOTI_BANK = 'modhumoti_bank';
    const MUTUAL_TRUST_BANK = 'mutual_trust_bank';
    const NATIONAL_BANK = 'national_bank';
    const NRB_BANK = 'nrb_bank';
    const NRB_COMMERCIAL_BANK = 'nrb_commercial_bank';
    const NRB_GLOBAL_BANK = 'nrb_global_bank';
    const ONE_BANK = 'one_bank';
    const PADMA_BANK = 'padma_bank';
    const PREMIER_BANK = 'premier_bank';
    const PRIME_BANK = 'PRIME_BANK';
    const PUBALI_BANK = 'pubali_bank';
    const SHIMANTO_BANK = 'shimanto_bank';
    const SOUTH_BANGLA_AGRICULTURE_AND_COMMERCE_BANK = 'south_bangla_agriculture_and_commerce_bank';
    const STANDARD_BANK = 'standard_bank';
    const TRUST_BANK = 'trust_bank';
    const UNITED_COMMERCIAL_BANK = 'united_commercial_bank';
    const UTTARA_BANK = 'uttara_bank';
    const SOUTHEAST_BANK = 'southeast_bank';
    const COMMUNITY_BANK_BANGLADESH = 'community_bank_bangladesh';
    const MERCANTILE_BANK = 'mercantile_bank';
    const NATIONAL_CREDIT_AND_COMMERCE_BANK = 'national_credit_and_commerce_bank';
    const JANATA_BANK = 'janata_bank';
    const AGRANI_BANK = 'agrani_bank';
    const RUPALI_BANK = 'rupali_bank';
    const BASIC_BANK = 'basic_bank';
    const BANGLADESH_DEVELOPMENT_BANK = 'bangladesh_development_bank';
    const BANGLADESH_KRISHI_BANK = 'bangladesh_krishi_bank';
    const RAJSHAHI_KRISHI_UNNAYAN_BANK = 'rajshahi_krishi_unnayan_bank';
    const PROBASHI_KALLYAN_BANK = 'probashi_kallyan_bank';
    const STANDARD_CHARTERED_BANK = 'standard_chartered_bank';
    const BANK_AL_FALAH = 'bank_al_falah';
    const AL_ARAFAH_ISLAMI_BANK = 'al_arafah_islami_bank';
    const EXIM_BANK = 'exim_bank';
    const FIRST_SECURITY_ISLAMI_BANK = 'first_security_islami_bank';
    const ICB_ISLAMIC_BANK = 'icb_islamic_bank';
    const ISLAMI_BANK_BANGLADESH = 'islami_bank_bangladesh';
    const SHAHJALAL_ISLAMI_BANK = 'shahjalal_islami_bank';
    const SOCIAL_ISLAMI_BANK = 'social_islami_bank';
    const UNION_BANK = 'union_bank';
    const COMMERCIAL_BANK_OF_CEYLON = 'commercial_bank_of_ceylon';

    public static function getPublishedBank()
    {
        return [self::CITY_BANK, self::BRAC_BANK, self::AB_BANK];
    }
}
