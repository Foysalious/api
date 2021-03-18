<?php

namespace App\Sheba\NeoBanking\Constants;


use Sheba\Helpers\ConstGetter;

final class ThirdPartyLog
{
    use ConstGetter;

    //from list
    const PBL_REQUEST = 'pbl_request';
    const PBL_RESPONSE = 'pbl_response';
    const PBL_ACCOUNT_CREATION = 'pbl_account_creation';
    const SBS = 'sbs';

}