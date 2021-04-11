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
        "entry_at" => $data->date,
        "details" => $data->note];
        $url = "api/journals/partner/".$data->partner->id;
        try {
            return $this->post($url, $entry_data);
        } catch (AccountingEntryServerError $e) {
            logError($e);
        }
    }

    public function storeExpenseEntry($data) {
        $expense_data = [
            "amount" => $data->has("amount_cleared") ? $data->amount_cleared : $data->amount,
            "source_type" => "expense",
            "debit_account_key" => $data->from_account_key,
            "credit_account_key" => $data->to_account_key,
            "entry_at" => $data->date,
            "note" => $data->note,
            "customer_id" =>$data->customer_id
            ];
        dd($expense_data);
        $url = "api/entries/partner/".$data->partner->id;
        try {
            return $this->post($url, $expense_data);
        } catch (AccountingEntryServerError $e) {
            logError($e);
        }
    }
}