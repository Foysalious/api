<?php namespace Tests\Mocks;

use Sheba\Sms\SmsVendor;

class MockSmsVendor implements SmsVendor
{

    public function send($mobile, $msg)
    {
        // TODO: Implement send() method.
    }

    public function getSingleSmsCost()
    {
        // TODO: Implement getSingleSmsCost() method.
    }
}