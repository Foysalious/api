<?php namespace Sheba\TopUp\Gateway\FailedReason;

use Throwable;

class RobiFailedReason extends PretupsFailedReason
{
    public function getReason()
    {
        try {
            $transaction_details = json_decode($this->transaction, true);
            if (array_key_exists('response', $transaction_details)) {
                if (array_key_exists('MESSAGE', $transaction_details['response'])) {
                    return $transaction_details['response']['MESSAGE'];
                }
            }
            return $transaction_details['message'];
        } catch (Throwable $e) {
            logError($e);
        }

        return "The Recharge could not be processed due to a technical issue. Pls try again later.";
    }
}
