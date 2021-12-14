<?php namespace Sheba\TopUp\Gateway\FailedReason;

use Sheba\TopUp\Gateway\FailedReason;
use Throwable;

class PaywellFailedReason extends FailedReason
{
    public function getReason()
    {
        try {
            $transaction_details = json_decode($this->transaction, true);
            if (array_key_exists('response', $transaction_details)) {
                if (!$transaction_details['response']) return "The Recharge could not be processed due to a technical issue. Pls try again later.";
                if (array_key_exists('message', $transaction_details['response']))
                    return $transaction_details['response']['message'];
            }
        } catch (Throwable $e) {
            logError($e);
        }

        return "The Recharge could not be processed due to a technical issue. Pls try again later.";
    }
}
