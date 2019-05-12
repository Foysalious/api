<?php namespace Sheba\Reports\Data;

use App\Models\CustomerTransaction;

class ShebaCredit extends Transaction
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getTransactions()
    {
        return CustomerTransaction::with('customer.profile', 'event')->get();
    }

    /**
     * @param CustomerTransaction $transaction
     * @return array
     */
    protected function mapForView($transaction)
    {
        $details = json_decode($transaction->transaction_details);
        $gateway = isset($details->gateway) && $details->gateway ?: null;
        if (!$gateway && isset($details->card_issuer)) {
            $gateway = $details->card_issuer;
        }

        return [
            'customer_id' => $transaction->customer_id,
            'customer_name' => !empty($transaction->customer->profile_id) ? $transaction->customer->profile->name : 'N/A',
            'mobile' => !empty($transaction->customer->profile_id) ? '`' . $transaction->customer->profile->mobile . '`' : 'N/A',
            'event_type' => $transaction->event_type,
            'event_id' => $transaction->event_id,
            'order_id' => (!empty($transaction->event_type) && $transaction->event_type == "App\Models\PartnerOrder") ? $transaction->event->order_id : 'N/A',
            'credit' => $transaction->type == 'Credit' ? $transaction->amount : '',
            'debit' => $transaction->type == 'Debit' ? $transaction->amount : '',
            'balance' => $transaction->balance,
            'log' => $transaction->log ,
            'log_type' => implode(' ', $transaction->logType()),
            'gateway' => $gateway,
            'created_at' => $transaction->created_at->format('Y-m-d h:i A')
        ];
    }

    protected function getFields()
    {
        return ['customer_id', 'type', 'amount'];
    }
}