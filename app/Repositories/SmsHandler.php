<?php

namespace App\Repositories;

use App\Library\Sms;
use App\Models\SmsTemplate;

class SmsHandler
{
    private $template;

    public function __construct($event_name)
    {
        $this->template = SmsTemplate::where('event_name', $event_name)->first();
    }

    public function send($mobile, $variables)
    {
        if ($this->template->is_on){
            $this->checkVariables($variables);

            $message = $this->template->template;
            foreach ($variables as $variable => $value) {
                $message = str_replace("{{" . $variable. "}}", $value, $message);
            }
            Sms::send_single_message($mobile, $message); //SMS***
        }
    }

    private function checkVariables($variables)
    {
        if (count(array_diff(explode(';', $this->template->variables), array_keys($variables)))){
            throw new \Exception("Variable doesn't match");
        }
    }
}