<?php namespace App\Helper;

class BangladeshiMobileValidator
{
    public static function validate($number)
    {
        if(self::isBangladeshiNumberFormat(formatMobileAux($number)))
            return true;

        return false;
    }

    private static function isBangladeshiNumberFormat($number)
    {
        return self::contains88($number)
        && strlen($number) == 14
        && $number[4] == "1"
        && self::inBdNumberDomain($number);
    }

    private static function contains88($number)
    {
        return substr($number, 0, 4) == "+880";
    }

    private static function inBdNumberDomain($number)
    {
        return in_array($number[5], [5,6,7,8,9]);
    }

}