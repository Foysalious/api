<?php namespace Sheba\Voucher;

use App\Models\Voucher;
use Sheba\BanglaToEnglish;

class VoucherCodeGenerator
{
    public static function byName($name)
    {
        if ($name == '') {
            $name = 'TMWNN';
        }
        $voucher = new VoucherCodeGenerator();
        return $voucher->generate(BanglaToEnglish::convert($name));
    }

    private function formatName($name)
    {
        if (preg_match("/^(Md.|Md|Mr.|Mr|Mrs.|Mrs|engr.|eng)/i", $name)) {
            return trim(preg_replace('/^(Md.|Md|Mr.|Mr|Mrs.|Mrs|engr.|eng)/i', '', $name));
        }
        return $name;
    }

    private function generate($name)
    {
        $name = $this->formatName($name);
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
            $this->generateUniqueVoucher($name);
        }
    }
}