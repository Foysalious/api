<?php namespace App\Sheba\SmsCampaign\InfoBip;

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
        $body = ['from' => 'Sheba.xyz', 'to' => $to, 'text' => $message];
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
            $response = $this->infoBip->get('/sms/2/logs', ['messageId' => $message_id]);
            if(isset($response['results']))
                if(isset($response['results'][0]))
                    return $response['results'][0];
            else return null;
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