<?php namespace App\Repositories;

use App\Models\SmsTemplate;
use Exception;
use Sheba\Sms\Sms;

class SmsHandler {
    private $template;
    private $sms;

    /** @var Sms */
    public function __construct($event_name) {
        $this->template = SmsTemplate::where('event_name', $event_name)->first();
        $this->sms      = new Sms(); //app(Sms::class);
    }

    public function setVendor($vendor) {
        $this->sms->setVendor($vendor);
        return $this;
    }

    /**
     * @param $mobile
     * @param $variables
     * @return Sms
     * @throws Exception
     */
    public function send($mobile, $variables) {
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
    public function setMessage($variables) {
        $this->checkVariables($variables);

        $message = $this->template->template;
        foreach ($variables as $variable => $value) {
            $message = str_replace("{{" . $variable . "}}", $value, $message);
        }
        $this->sms->msg($message);
        return $this;
    }

    public function getCost() {
        return $this->sms->getCost();
    }

    public function setMobile($mobile) {
        $this->sms->to($mobile);
        return $this;
    }

    public function shoot() {
        $this->sms->shoot();
        return $this->sms;
    }

    private function checkVariables($variables) {
        if (count(array_diff(explode(';', $this->template->variables), array_keys($variables)))) {
            throw new Exception("Variable doesn't match");
        }
    }
}
