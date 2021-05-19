<?php namespace Sheba\SmsCampaign;

use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\Sms\Sms;
use Sheba\SmsCampaign\DTO\VendorSmsDTO;

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
        $sms = $this->sms->to($to)->msg($message)->setBusinessType(BusinessType::SMANAGER)->setFeatureType(FeatureType::SMS_CAMPAIGN);
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