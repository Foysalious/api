<?php namespace Sheba;

use Sheba\Helpers\Converters\BanglaToEnglish as BanglaToEnglishReal;

/**
 * Proxy class as this namespace is used by voucher package.
 * Fix it whenever possible.
 */
class BanglaToEnglish
{
    /**
     * Convert unicode string to English
     *
     * @param  string $str The string to convert
     * @return string      The converted string
     */
    public static function convert($str)
    {
        return BanglaToEnglishReal::convert($str);
    }
}