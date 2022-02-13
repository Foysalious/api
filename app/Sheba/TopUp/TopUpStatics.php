<?php

namespace Sheba\TopUp;

class TopUpStatics
{
    public static function topUpOTFRequestValidate(): array
    {
        return [
            'sim_type' => 'required|in:prepaid,postpaid',
            'for' => 'required|in:customer,partner,affiliate',
            'vendor_id' => 'required|exists:topup_vendors,id',
        ];
    }

    public static function topUpOTFDetailsValidate(): array
    {
        return [
            'for' => 'required|in:customer,partner,affiliate',
            'vendor_id' => 'required|exists:topup_vendors,id',
            'otf_id' => 'required|integer'
        ];
    }
}