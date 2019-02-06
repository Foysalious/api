<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 2/6/2019
 * Time: 1:49 PM
 */

namespace App\Sheba\SmsCampaign\InfoBip;


use Sheba\SmsCampaign\InfoBip\InfoBip;

class SmsHandler
{
    private $infoBip;

    public function __construct(InfoBip $infoBip)
    {
        $this->infoBip = $infoBip;
    }

    public function sendBulkMessages($to, $message)
    {
        $body = [
            'from' => 'Sheba.xyz',
            'to' => $to,
            'text' => $message
        ];
        return $this->infoBip->post('/sms/2/text/single',$body);
    }
    
}