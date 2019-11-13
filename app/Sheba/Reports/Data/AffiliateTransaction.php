<?php namespace Sheba\Reports\Data;

use App\Models\AffiliateTransaction as AffiliateTransactionModel;
use Illuminate\Database\Eloquent\Collection;

class AffiliateTransaction extends Transaction
{
    /**
     * @return Collection
     */
    protected function getTransactions()
    {
        return AffiliateTransactionModel::with('affiliate.profile')->get();
    }

    /**
     * @param AffiliateTransactionModel $transaction
     * @return array
     */
    protected function mapForView($transaction)
    {
        $gateway = '';
        $sender = '';
        $receiver = '';
        $type = null;
        $gateway_transaction_id = '';
        if ($transaction->transaction_details) {
            $transaction_details = json_decode($transaction->transaction_details, true);
            $gateway_transaction_id = isset($transaction_details['details']['transaction_id']) ? $transaction_details['details']['transaction_id'] : $transaction_details['transaction_id'];
            $gateway = isset($transaction_details['name']) ? $transaction_details['name'] : $transaction_details['gateway'];
            if ($gateway == 'Bkash') {
                $sender = $transaction_details['details']['details']['transaction']['sender'];
                $receiver = $transaction_details['details']['details']['transaction']['receiver'];
                $sender = "`$sender`";
                $receiver = "`$receiver`";
            }
        }

        return [
            "transaction_id" => $transaction->id,
            "affiliate_id" => $transaction->affiliate_id,
            "affiliate_name" => $transaction->affiliate->profile->name,
            "is_ambassador" => $transaction->affiliate->is_ambassador ? "Yes" : "No",
            "affiliation_type" => $transaction->affiliation_type,
            "affiliation_id" => $transaction->affiliation_id,
            "is_gifted" => $transaction->is_gifted ? 'Yes' : 'No',
            "log" => $transaction->log,
            "log_type" => implode(' ', $transaction->logType()),
            "transaction_details" => $transaction->transaction_details,
            "gateway" => $gateway,
            "sender" => $sender,
            "receiver" => $receiver,
            "gateway_transaction_id" => $gateway_transaction_id,
            "debit" => $transaction->type == "Debit" ? $transaction->amount : '',
            "credit" => $transaction->type == "Credit" ? $transaction->amount : '',
            "balance" => $transaction->balance,
            "created_by" => $transaction->created_by,
            "created_by_name" => $transaction->created_by_name,
            "created_at" => $transaction->created_at->format('d M Y h:i A'),
            "updated_at" => $transaction->updated_at ? $transaction->updated_at->format('d M Y h:i A') : 'N/A'
        ];
    }

    protected function getFields()
    {
        return ['affiliate_id', 'type', 'amount'];
    }
}
