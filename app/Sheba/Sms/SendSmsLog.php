<?php namespace App\Sheba\Sms;

use Sheba\Dal\SmsSendingLog\Model as SmsSendingLog;
use Sheba\ModificationFields;

class SendSmsLog
{
    use ModificationFields;
    private $businessType;
    private $featureType;
    private $smsBody;
    private $smsTemplate;
    private $mobileNumber;
    private $status;
    private $smsSendingLog;
    private $smsCost;

    /**
     * @param $businessType
     * @return $this
     */
    public function setBusinessType($businessType) {
        $this->businessType = $businessType;
        return $this;
    }

    /**
     * @param $featureType
     * @return $this
     */
    public function setFeatureType($featureType) {
        $this->featureType = $featureType;
        return $this;
    }

    /**
     * @param $smsBody
     * @return $this
     */
    public function setSmsBody($smsBody) {
        $this->smsBody = $smsBody;
        return $this;
    }

    /**
     * @param $smsTemplate
     * @return $this
     */
    public function setSmsTemplate($smsTemplate) {
        $this->smsTemplate = $smsTemplate;
        return $this;
    }

    /**
     * @param $mobile
     * @return $this
     */
    public function setMobile($mobile) {
        $this->mobileNumber = $mobile;
        return $this;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * @return bool
     */
    public function store() {
        try {
            $this->smsSendingLog = new SmsSendingLog($this->getData());
            return $this->smsSendingLog->save();
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }
    }

    private function getData() {
        return [
            "sms_body" => $this->smsBody,
            "feature_name" => $this->featureType,
            "business_name" => $this->businessType,
            "sms_template" => $this->smsTemplate,
            "mobile_number" => $this->mobileNumber,
            "sms_status" => $this->status,
            "sms_cost" => $this->smsCost
        ];
    }

    /**
     * @param $smsCost
     * @return $this
     */
    public function setSmsCost($smsCost)
    {
        $this->smsCost = $smsCost;
        return $this;
    }
}