<?php namespace Sheba\Voucher;

use Sheba\BanglaToEnglish;

class VoucherCodeGenerator
{
    public static function byName($name = "TMWNN")
    {
        return BanglaToEnglish::convert($name);
    }
}