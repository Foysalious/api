<?php namespace Sheba\Voucher;

use App\Models\Voucher;
use Sheba\BanglaToEnglish;

class VoucherCodeGenerator
{
    public static function byName($name)
    {
        $voucher = new VoucherCodeGenerator();
        if ($name == '') {
            return $voucher->generate('TMWNN');
        }
        $name_format = new NameFormatter(BanglaToEnglish::convert($name));
        return $voucher->generate($name_format->removeUnicodeCharactersAndFormatName());
    }


    private function generate($name)
    {
        $words = explode(' ', $name);
        foreach ($words as $word) {
            if (strlen($word) < 3) {
                continue;
            }
            if (strlen($word) > 8) {
                $name = substr($word, 0, 8);
            } else {
                $name = $word;
            }
            return $this->generateUniqueVoucher($name);
        }
        return $this->generateUniqueVoucher(str_random(2));
    }

    private function generateUniqueVoucher($name)
    {
        $code = strtoupper($name . rand(10, 99) . randomString(2, 0, 1, 0));
        if (Voucher::where('code', $code)->first() == null) {
            return $code;
        } else {
            return $this->generateUniqueVoucher($name);
        }
    }
}