<?php namespace Sheba\SmsCampaign;

use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Sheba\Sms\Sms;
use Sheba\Sms\SmsService\BulkSmsResponse;
use Sheba\Sms\SmsService\SingleSmsResponse;
use Sheba\Sms\SmsService\SmsService;

class SmsHandler
{
    /** @var SmsService $smsService */
    private $smsService;

    public function __construct(SmsService $sms)
    {
        $this->smsService = $sms;
    }

    /**
     * @param $to
     * @param $message
     * @return BulkSmsResponse
     */
    public function sendBulkMessages($to, $message)
    {
        $sms = (new Sms())
            ->setBusinessType(BusinessType::SMANAGER)
            ->setFeatureType(FeatureType::SMS_CAMPAIGN)
            ->to($to)
            ->message($message);
        return $sms->shoot();
    }

    /**
     * @param $to
     * @param $message
     * @return double
     */
    public function getBulkCharge($to, $message)
    {
        $sms = (new Sms())
            ->setBusinessType(BusinessType::SMANAGER)
            ->setFeatureType(FeatureType::SMS_CAMPAIGN)
            ->to($to)
            ->message($message);
        return $sms->estimateCharge()->getTotalCharge();
    }

    /**
     * @param $message_id
     * @return SingleSmsResponse
     */
    public function getSingleMessage($message_id)
    {
        return $this->smsService->getStatus($message_id);
    }
}
