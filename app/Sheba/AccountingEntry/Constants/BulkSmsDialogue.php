<?php

namespace App\Sheba\AccountingEntry\Constants;

use Sheba\Helpers\ConstGetter;

class BulkSmsDialogue
{
    use ConstGetter;
    const FREE_SMS_DIALOGUE = ' জন কাস্টমারের নিকট ফ্রী তে তাগাদা পাঠানো হবে!';
    const SMS_FREE_AND_CHARGING_BOTH_DIALOGUE = ' জন কাস্টমারের নিকট ফ্রী তে তাগাদা পাঠানো হবে, বাকি টাকা আপনার একাউন্ট থেকে চার্জ করা হবে!';
    const SMS_CHARGING_DIALOGUE = ' জন কাস্টমারের নিকট তাগাদা পাঠাতে আপনার অ্যাকাউন্ট থেকে চার্জ করা হবে!';
}