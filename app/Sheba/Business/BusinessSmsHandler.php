<?php namespace Sheba\Business;

use App\Sheba\Sms\FeatureType;
use Sheba\Sms\Sms;
use App\Models\BusinessSmsTemplate;

class BusinessSmsHandler
{
    /** @var BusinessSmsTemplate $template */
    private $template;
    /** @var Sms $sms */
    private $sms;

    public function __construct($event_name)
    {
        $this->template = BusinessSmsTemplate::where('event_name', $event_name)->first();
        $this->sms = app(Sms::class);
    }

    /**
     * @param $mobile
     * @param $variables
     * @return Sms
     * @throws \Exception
     */
    public function send($mobile, $variables)
    {
        if (!$this->template->is_published) return;

        $this->checkVariables($variables);

        $message = $this->template->template;
        foreach ($variables as $variable => $value) {
            $message = str_replace("{{" . $variable . "}}", $value, $message);
        }
        $sms = $this->sms
            ->setFeatureType(FeatureType::BUSINESS)
            ->setBusinessType(FeatureType::BUSINESS)
            ->to($mobile)
            ->msg($message);
        $sms->shoot();

        return $sms;
    }

    /**
     * @param $variables
     * @throws \Exception
     */
    private function checkVariables($variables)
    {
        if (count(array_diff(explode(';', $this->template->variables), array_keys($variables)))) {
            throw new \Exception("Variable doesn't match");
        }
    }
}