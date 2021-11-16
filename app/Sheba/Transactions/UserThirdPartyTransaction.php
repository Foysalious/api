<?php

namespace Sheba\Transactions;

trait UserThirdPartyTransaction 
{
    public function thirdPartyTransactionId($transactionDetails)
    {
        $third_party_transaction_id = null;
        $transaction_details = json_decode($transactionDetails, true);
        if (!isset($transaction_details['transaction_id'])) {
            return null;
        }
        if (isset($transaction_details['name']) && strtolower($transaction_details['name']) == 'manual') {
            $third_party_transaction_id = $transaction_details['details']['transaction_id'] ?? null;
        }
        if (str_contains(strtolower($transaction_details['transaction_id']), 'sheba_nagad') ) {
            $third_party_transaction_id = $transaction_details['details']['issuerPaymentRefNo'] ?? null;
        }
        if (str_contains(strtolower($transaction_details['transaction_id']), 'sheba_bkash') ) {
            $third_party_transaction_id = $transaction_details['details']['trxID'] ?? null;
        }
        if (str_contains(strtolower($transaction_details['transaction_id']), 'sheba_port_wallet') ) {
            $third_party_transaction_id = $transaction_details['details']['data']['invoice_id'] ?? null;
        }
        if (str_contains(strtolower($transaction_details['transaction_id']), 'sheba_ssl') ) {
            $third_party_transaction_id = $transaction_details['transaction_id'] ?? null;
        }
        if (str_contains(strtolower($transaction_details['transaction_id']), 'sheba_cbl') ) {
            $third_party_transaction_id = $transaction_details['details']['Response']['Order']['row']['id'] ?? null;
        }
        if (str_contains(strtolower($transaction_details['transaction_id']), 'sheba_ebl') ) {
            $third_party_transaction_id = $transaction_details['details']['data']['params']['transaction_id'] ?? null;
        }
        if (str_contains(strtolower($transaction_details['transaction_id']), 'sheba_ok_wallet') ) {
            $third_party_transaction_id = $transaction_details['details']['client_response']['transactionID'] ?? null;
        }
        return $third_party_transaction_id;
    }

    public function thirdPartyGateway($transactionDetails)
    {
        $details = json_decode($transactionDetails, true);
        return $details['gateway'] ?? null;
    }
}