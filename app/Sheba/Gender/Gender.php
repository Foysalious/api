<?php namespace Sheba\Gender;

use Sheba\Helpers\ConstGetter;

class Gender
{
    use ConstGetter;

    const MALE = 'Male';
    const MALE_BN = 'পুরুষ';
    const FEMALE = 'Female';
    const FEMALE_bn = 'নারী';
    const FEMALE_bn_alt = 'মহিলা';
    const OTHER = 'Other';
    const OTHER_alt = 'Others';
    const OTHER_bn = 'অন্যান্য';

    public static function getAllEnglishWithKeys()
    {
        return [
            'MALE' => self::MALE,
            'FEMALE' => self::FEMALE,
            'OTHER' => self::OTHER,
        ];
    }

    public static function getAllEnglish()
    {
        return array_values(static::getAllEnglishWithKeys());
    }

    public static function implodeEnglish($glue = ',')
    {
        return implode($glue, static::getAllEnglish());
    }

    public static function getGenderNameInBangla($gender)
    {
        if ($gender === self::MALE) return self::MALE_BN;
        if ($gender === self::FEMALE) return self::FEMALE_bn;
        if ($gender === self::OTHER) return self::OTHER_bn;
        return $gender;
    }

    public static function convertBanglaToEnglish($gender)
    {
        if ($gender === self::MALE_BN) return self::MALE;
        if ($gender === self::FEMALE_bn) return self::FEMALE;
        if ($gender === self::FEMALE_bn_alt) return self::FEMALE;
        if ($gender === self::OTHER_bn) return self::OTHER;
        return $gender;
    }

    public static function getGenderDisplayableName($gender)
    {
        if ($gender === self::MALE) return ['en' => self::MALE, 'bn' => self::MALE_BN];
        if ($gender === self::FEMALE) return ['en' => self::FEMALE, 'bn' => self::FEMALE_bn];

        return ['en' => self::OTHER, 'bn' => self::OTHER_bn];
    }

    public static function toList()
    {
        return [
            [
                'key' => 'female',
                'en' => self::FEMALE,
                'bn' => self::FEMALE_bn
            ],
            [
                'key' => 'male',
                'en' => self::MALE,
                'bn' => self::MALE_BN
            ],
            [
                'key' => 'other',
                'en' => self::OTHER,
                'bn' => self::OTHER_bn
            ]
        ];
    }
}
