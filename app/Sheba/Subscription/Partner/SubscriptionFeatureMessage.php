<?php

namespace Sheba\Subscription\Partner;

class SubscriptionFeatureMessage
{
    private $features_bangla_keyword = [
        'topup' => "টপ-আপ",
        'sms' => "ফ্রি-এসএমএস",
        'delivery' => "এস-ডেলিভারি"
    ];
    private $message = "আপনার নির্ধারিত প্যাকেজের %s সংখ্যার লিমিট অতিক্রম করেছে। অনুগ্রহ করে প্যাকেজ আপগ্রেড করুন অথবা পরবর্তী মাস শুরু পর্যন্ত অপেক্ষা করুন।";

    public function getMessage(array $features)
    {
        if (count($features) == 1) {
            return sprintf($this->message, $this->features_bangla_keyword[$features[0]]);
        } elseif (count($features) == 2) {
            $features_name = $this->features_bangla_keyword[$features[0]] . " এবং " . $this->features_bangla_keyword[$features[1]];
            return sprintf($this->message, $features_name);
        } elseif (count($features) == 3) {
            $features_name = $this->features_bangla_keyword[$features[0]] . ", " . $this->features_bangla_keyword[$features[1]] . " এবং " . $this->features_bangla_keyword[$features[2]];
            return sprintf($this->message, $features_name);
        }

        return [];
    }
}