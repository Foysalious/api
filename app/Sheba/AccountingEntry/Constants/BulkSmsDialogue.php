<?php

namespace App\Sheba\AccountingEntry\Constants;

use Sheba\Helpers\ConstGetter;

class BulkSmsDialogue
{
    use ConstGetter;
    const FREE_SMS_DIALOGUE = ' জন কাস্টমারের নিকট তাগাদা পাঠানো হবে!';
    const SHORTAGE_OF_SMS_WITH_CUSTOMER_COUNT = ' জন কাস্টমারের নিকট তাগাদা পাঠানোর জন্য পর্যাপ্ত এসএমএস নেই ';
    const SHORTAGE_OF_SMS = 'তাগাদা পাঠানোর জন্য পর্যাপ্ত এসএমএস নেই';
}