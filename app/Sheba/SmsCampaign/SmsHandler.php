<?php namespace Sheba\SmsCampaign;

use GuzzleHttp\Exception\GuzzleException;
use Sheba\Sms\Sms;
use Sheba\SmsCampaign\DTO\VendorSmsDTO;

class SmsHandler
{
    /** @var Sms $sms */
    private $sms;
    /** @var bool */
    private $isOff;

    public function __construct(Sms $sms)
    {
        $this->sms = $sms->setVendor('infobip');
        $this->isOff = !config('sms.is_on');
    }

    /**
     * @param $to
     * @param $message
     * @return mixed
     */
    public function sendBulkMessages($to, $message)
    {
        if ($this->isOff) return;
        $sms = $this->sms->to($to)->msg($message);
        $sms->shoot();
        return $sms->getVendorResponse();
    }

    /**
     * @param $message_id
     * @return VendorSmsDTO
     */
    public function getSingleMessage($message_id)
    {
        $response = $this->sms->get(['messageId' => $message_id]);
        $single_sms = new VendorSmsDTO();
        if ($response && is_array($response->results) && !empty($response->results)) {
            $single_sms->setResponse($response->results[0]);
        }
        return $single_sms;
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