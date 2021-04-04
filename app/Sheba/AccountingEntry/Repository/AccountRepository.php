<?php namespace App\Sheba\AccountingEntry\Repository;

use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class AccountRepository extends AccountingEntryClient
{
    public function accountTransfer($data){
        $entry_data = [
        "amount" => $data->amount,
        "source_id" => rand(0000,9999).date('s').preg_replace("/^.*\./i","", microtime(true)),
        "source_type" => "transfer",
        "debit_account_key" => $data->from_account_key,
        "credit_account_key" => $data->to_account_key,
        "entry_at" => $data->date
    ];
        $url = "api/journals/partner/".$data->partner->id;
        try {
            return $this->post($url, $entry_data);
        } catch (AccountingEntryServerError $e) {
            logError($e);
        }
    }
}