<?php namespace App\Repositories;

use Exception;
use Sheba\Dal\SmsTemplate\Contract as SmsTemplateRepo;
use Sheba\Dal\SmsTemplate\Model as SmsTemplate;
use Sheba\Sms\Sms;
use Sheba\Sms\SmsService\SingleSmsResponse;

class SmsHandler
{
    /** @var SmsTemplate  */
    private $template;
    /** @var Sms  */
    private $sms;
    /** @var bool */
    private $isOff;

    /** @var Sms */
    public function __construct($event_name)
    {
        /** @var SmsTemplateRepo $sms_templates */
        $sms_templates  = app(SmsTemplateRepo::class);
        $this->template = $sms_templates->findByEventName($event_name);
        $this->sms      = app(Sms::class);
        $this->isOff    = !config('sms.is_on');
    }

    /**
     * @param $mobile
     * @param $variables
     * @return SingleSmsResponse | void
     * @throws Exception
     */
    public function send($mobile, $variables)
    {
        if ($this->isOff()) return;

        $this->setMessage($variables);
        $this->setMobile($mobile);
        return $this->shoot();
    }

    private function isOff()
    {
        return $this->isOff || !$this->template->is_on;
    }

    /**
     * @param $variables
     * @return SmsHandler
     * @throws Exception
     */
    public function setMessage($variables)
    {
        $this->checkVariables($variables);

        $message = $this->template->template;
        foreach ($variables as $variable => $value) {
            $message = str_replace("{{" . $variable . "}}", $value, $message);
        }
        $this->sms->msg($message);
        return $this;
    }

    /**
     * @return double
     */
    public function estimateCharge()
    {
        return $this->sms->estimateCharge()->getTotalCharge();
    }

    public function setMobile($mobile)
    {
        $this->sms->to($mobile);
        return $this;
    }

    /**
     * @return SingleSmsResponse | void
     */
    public function shoot()
    {
        if ($this->isOff) return;

        return $this->sms->shoot();
    }

    private function checkVariables($variables)
    {
        if ($this->template->doesVariablesMatch($variables)) return;

        throw new Exception("Variable doesn't match");
    }

    /**
     * @param $businessType
     * @return $this
     */
    public function setBusinessType($businessType)
    {
        $this->sms->setBusinessType($businessType);
        return $this;
    }

    /**
     * @param $featureType
     * @return $this
     */
    public function setFeatureType($featureType)
    {
        $this->sms->setFeatureType($featureType);
        return $this;
    }
}
