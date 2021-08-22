<?php

namespace Sheba\NeoBanking\Statics;

use Sheba\Helpers\ConstGetter;

class PBLStatics
{
    use ConstGetter;

    const CHANNEL                  = "SHEBA";
    const CHEQUE_BOOK              = "Yes";
    const INTERNET_BANKING         = "Yes";
    const DEBIT_CARD               = "Yes";
    const LEGAL_DOC_NAME           = "TRADE.LICENSE";
    const EKYC_VERIFIED            = "Yes";
    const NATIONAL_ID              = "NATIONAL.ID";
    const BIRTH_CERTIFICATE        = "BIRTH.CERTIFICATE";
    const PASSPORT_NUMBER          = "PASSPORT";
    const DEFAULT_BRANCH           = "BD0010104";
    const NID_NUMBER               = "NATIONAL.ID";
    const BIRTH_CERTIFICATE_NUMBER = "BIRTH.CERTIFICATE";
    const ONBOARD_TYPE             = 2;

    /**
     * @return string
     * @throws \Exception
     */
    public static function uniqueTransactionId(): string
    {
        return self::CHANNEL . "-" . time() . randomString(4, 1, 1);
    }

    /**
     * @param $value
     * @return string
     */
    public static function identificationTypeGenerate($value): string
    {
        if ($value === "birth_certificate_number") return self::BIRTH_CERTIFICATE;
        if ($value === "passport_number") return self::PASSPORT_NUMBER;
        return self::NATIONAL_ID;
    }

    public static function fromKey($key)
    {
        $all = self::getWithKeys();
        return $all[strtoupper($key)];
    }
}
