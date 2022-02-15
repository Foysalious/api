<?php

namespace App\Sheba\AccountingEntry\Helper;

use Carbon\Carbon;

class AccountingHelper
{
    static function convertStartDate($date = null)
    {
        return $date ?
            Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 0:00:00')->timestamp :
            strtotime('1 January 1971');
    }

    static function convertEndDate($date = null)
    {
        return $date ?
            Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 23:59:59')->timestamp :
            strtotime('tomorrow midnight') - 1;
    }
}