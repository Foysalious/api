<?php

namespace App\Sheba\NeoBanking\Constants;


final class ThirdPartyLog
{
    //data type list
    const REQUEST_DATA_TYPE = 'request';
    const RESPONSE_DATA_TYPE = 'response';

    //from list
    const PBL_REQUEST = 'pbl_request';
    const PBL_RESPONSE = 'pbl_response';

    const DATA_TYPE_LIST = [
        self::RESPONSE_DATA_TYPE,
        self::REQUEST_DATA_TYPE
    ];

    const THIRD_PARTY_FROM_LIST = [
        self::PBL_REQUEST,
        self::PBL_RESPONSE
    ];
}