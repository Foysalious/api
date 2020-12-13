<?php namespace Sheba\TopUp\FailedReason;

class BanglalinkFailedReason extends PretupsFailedReason
{
    public function getReason()
    {
        $transaction_details = json_decode($this->transaction, true);
        if ($transaction_details_response = $transaction_details['response']) {
            return explode(':',$transaction_details_response['MESSAGE'])[1];
        }
        return $transaction_details['message'];
    }
}