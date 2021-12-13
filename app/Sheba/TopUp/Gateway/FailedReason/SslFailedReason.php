<?php namespace Sheba\TopUp\Gateway\FailedReason;

use Sheba\TopUp\Gateway\FailedReason;
use Throwable;

class SslFailedReason extends FailedReason
{
    public function getReason()
    {
        try {
            $transaction_details = json_decode($this->transaction, true);
            if (array_key_exists('response', $transaction_details)) {
                if (!$transaction_details['response']) return "The Recharge could not be processed due to a technical issue. Pls try again later.";
                if (array_key_exists('message', $transaction_details['response'])) return $transaction_details['response']['message'];
                return $transaction_details['response']['MESSAGE'];
            }
            $recharge_response_codes = array_except(SslRechargeResponseCodes::messages(), $this->removedResponseCodes());
            if (array_key_exists($transaction_details['recharge_status'], $recharge_response_codes)) return $recharge_response_codes[$transaction_details['recharge_status']];
        } catch (Throwable $e) {
            logError($e);
        }

        return "The Recharge could not be processed due to a technical issue. Pls try again later.";
    }

    private function removedResponseCodes()
    {
        return [
            '100', '200', '201', '202', '203', '300', '301',
            '308', '309', '313', '314', '315', '316', '317',
            '318', '320', '321', '322', '323', '327', '331',
            '332', '333', '335', '336', '338', '340', '341',
            '342', '343', '344', '345', '349', '350', '351',
            '354', '380', '398', '400', '800', '900', '999',
        ];
    }
}
