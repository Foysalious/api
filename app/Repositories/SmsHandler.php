<?php namespace App\Repositories;

use App\Models\SmsTemplate;
use Sheba\Sms\Sms;

class SmsHandler
{
    private $template;
    private $sms; /** @var Sms */

    public function __construct($event_name)
    {
        $this->template = SmsTemplate::where('event_name', $event_name)->first();
        $this->sms = new Sms(); //app(Sms::class);
    }

    public function send($mobile, $variables)
    {
        if ($this->template->is_on){
            $this->checkVariables($variables);

            $message = $this->template->template;
            foreach ($variables as $variable => $value) {
                $message = str_replace("{{" . $variable. "}}", $value, $message);
            }
            $this->sms->shoot($mobile, $message);
        }
    }

    private function checkVariables($variables)
    {
        if (count(array_diff(explode(';', $this->template->variables), array_keys($variables)))){
            throw new \Exception("Variable doesn't match");
        }
    }
}