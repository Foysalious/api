<?php

namespace Sheba\Subscription\Partner;

class SubscriptionFeatureMessage
{
    private $features_bangla_keyword = [
        'topup' => "টপ-আপ",
        'sms' => "ফ্রি-এসএমএস",
        'delivery' => "এস-ডেলিভারি"
    ];
    private $message_body = "আপনার নির্ধারিত প্যাকেজের %s সংখ্যার লিমিট অতিক্রম করেছে। অনুগ্রহ করে প্যাকেজ আপগ্রেড করুন অথবা পরবর্তী মাস শুরু পর্যন্ত অপেক্ষা করুন।";

    public function getMessage(array $features)
    {
        $message = "";
        $features_length = count($features);
        $i = 0;
        while ($features_length) {
            if ($features_length == 1) {
                return sprintf($this->message_body, $this->features_bangla_keyword[$features[$i]]);
            } elseif ($features_length == 2) {
                $message = $message . $this->features_bangla_keyword[$features[$i]] . " এবং " . $this->features_bangla_keyword[$features[$i+1]];
                return sprintf($this->message_body, $message);
            } else {
                $message = $message . $this->features_bangla_keyword[$features[$i]] . ", ";
            }
            $i++;
            $features_length--;
        }

        return [];
    }
}