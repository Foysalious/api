<?php namespace App\Sheba\Gender;

use Sheba\Helpers\ConstGetter;

class Gender
{
    use ConstGetter;

    const MALE = 'Male';
    const FEMALE= 'Female';
    const OTHER = 'Other';
    const MALE_BN = 'পুরুষ';
    const FEMALE_bn = 'নারী';
    const OTHER_bn = 'অন্যান্য';


    public static function getGenderFormation($gender)
    {
        if ($gender === self::MALE)
            return self::MALE_BN;
        if ($gender === self::FEMALE)
            return self::FEMALE_bn;
        if ($gender === self::OTHER)
            return self::OTHER_bn;
    }
}
