<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 2/6/2019
 * Time: 1:49 PM
 */

namespace App\Sheba\SmsCampaign\InfoBip;


use GuzzleHttp\Exception\GuzzleException;
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
        try {
            return $this->infoBip->post('/sms/2/text/single', $body);
        } catch (GuzzleException $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response(request()->all(), null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }

    public function getSingleMessage($message_id)
    {
        try {
            return $this->infoBip->get('/sms/2/logs', ['messageId' => $message_id])['results'][0];
        } catch (GuzzleException $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response(request()->all(), null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }

    public function getMessagesByBulkId($bulk_id)
    {
        try {
            return $this->infoBip->get('/sms/2/logs', ['bulkId' => $bulk_id]);
        } catch (GuzzleException $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response(request()->all(), null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }
    
}