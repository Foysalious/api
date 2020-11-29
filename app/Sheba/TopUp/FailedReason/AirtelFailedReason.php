<?php namespace Sheba\TopUp\FailedReason;

class AirtelFailedReason extends PretupsFailedReason
{
    public function getReason()
    {
        $transaction_details = json_decode($this->transaction, true);
        if ($transaction_details_response = $transaction_details['response']) {
            return $transaction_details_response['MESSAGE'];
        }
        return $transaction_details['message'];
    }
}