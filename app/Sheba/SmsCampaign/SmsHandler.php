<?php namespace App\Sheba\SmsCampaign;

use GuzzleHttp\Exception\GuzzleException;
use Sheba\Sms\Sms;

class SmsHandler
{
    /** @var Sms $sms */
    private $sms;

    public function __construct(Sms $sms)
    {
        $this->sms = $sms->setVendor('infobip');
    }

    /**
     * @param $to
     * @param $message
     * @return mixed
     */
    public function sendBulkMessages($to, $message)
    {
        return $this->sms->shoot($to, $message);
    }

    /**
     * @param $message_id
     * @return mixed
     */
    public function getSingleMessage($message_id)
    {
        $response = $this->sms->get(['messageId' => $message_id]);
        return $response->results[0];
    }

    /**
     * @param $bulk_id
     * @return \Exception|GuzzleException|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getMessagesByBulkId($bulk_id)
    {
        return $this->sms->get(['bulkId' => $bulk_id]);
    }
}