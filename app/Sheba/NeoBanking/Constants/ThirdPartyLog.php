<?php

namespace App\Sheba\NeoBanking\Constants;


final class ThirdPartyLog
{
    //from list
    const PBL_REQUEST = 'pbl_request';
    const PBL_RESPONSE = 'pbl_response';
    const PBL_ACCOUNT_CREATION = 'pbl_account_creation';
    const SBS = 'sbs';

    const THIRD_PARTY_FROM_LIST = [
        self::PBL_REQUEST,
        self::PBL_RESPONSE,
        self::PBL_ACCOUNT_CREATION
    ];
}