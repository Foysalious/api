<?php namespace App\Sheba\Gender;

use Sheba\Helpers\ConstGetter;

class Gender
{
    use ConstGetter;

    const MALE = 'Male';
    const MALE_BN = 'পুরুষ';
    const FEMALE= 'Female';
    const FEMALE_bn = 'নারী';
    const OTHER = 'Other';
    const OTHER_bn = 'অন্যান্য';

    public static function getGenderNameInBangla($gender)
    {
        if ($gender === self::MALE) return self::MALE_BN;
        if ($gender === self::FEMALE) return self::FEMALE_bn;

        return self::OTHER_bn;
    }

    public static function getGenderDisplayableName($gender)
    {
        if ($gender === self::MALE) return ['en' => self::MALE, 'bn' => self::MALE_BN];
        if ($gender === self::FEMALE) return ['en' => self::FEMALE, 'bn' => self::FEMALE_bn];

        return ['en' => self::OTHER, 'bn' => self::OTHER_bn];
    }
}
