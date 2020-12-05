<?php namespace App\Http\Validators;

use Illuminate\Support\Facades\Request;
use Sheba\Dal\SmsTemplate\Contract as SmsTemplateRepo;
use Sheba\Dal\SmsTemplate\Model as SmsTemplate;

class SmsTemplateValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        /** @var SmsTemplateRepo $sms_templates */
        $sms_templates  = app(SmsTemplateRepo::class);
        /** @var SmsTemplate $template */
        $template = $sms_templates->find(Request::segment(2));

        foreach( $template->getVariables() as $variable) {
            if(!str_contains($value,  "{{" . $variable . "}}")) {
                return false;
            }
        }

        return true;
    }
}