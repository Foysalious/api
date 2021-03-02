<?php namespace Sheba\Subscription\Exceptions;


use Throwable;

class HasAlreadyCollectedFeeException extends \Exception
{
    public function __construct($message = 'আপনার প্যকেজ এর জন্য অগ্রিম ফি নেয়া আছে আপনার বর্তমান প্যকেজ এর মেয়াদ শেষ হলে স্বয়ংক্রিয়  ভাবে নবায়ন হয়ে যাবে', $code = 400, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
