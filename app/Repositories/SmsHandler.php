<?php namespace App\Repositories;

use Exception;
use Sheba\Dal\SmsTemplate\Contract as SmsTemplateRepo;
use Sheba\Dal\SmsTemplate\Model as SmsTemplate;
use Sheba\Sms\Sms;

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
        $this->isOff = !config('sms.is_on');
    }

    public function setVendor($vendor)
    {
        $this->sms->setVendor($vendor);
        return $this;
    }

    /**
     * @param $mobile
     * @param $variables
     * @return Sms
     * @throws Exception
     */
    public function send($mobile, $variables)
    {
        if ($this->isOff) return;
        if (!$this->template->is_on) return $this->sms;

        $this->checkVariables($variables);

        $message = $this->template->template;
        foreach ($variables as $variable => $value) {
            $message = str_replace("{{" . $variable . "}}", $value, $message);
        }
        $sms = $this->sms->to($mobile)->msg($message);
        $sms->shoot();

        return $sms;
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

    public function getCost()
    {
        return $this->sms->getCost();
    }

    public function setMobile($mobile)
    {
        $this->sms->to($mobile);
        return $this;
    }

    public function shoot()
    {
        if ($this->isOff) return;
        $this->sms->shoot();
        return $this->sms;
    }

    private function checkVariables($variables)
    {
        if ($this->template->doesVariablesMatch($variables)) return;

        throw new Exception("Variable doesn't match");
    }

    public function getMsg() {
        return $this->sms->getMsg();
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
