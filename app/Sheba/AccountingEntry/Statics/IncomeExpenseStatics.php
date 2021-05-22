<?php

namespace Sheba\AccountingEntry\Statics;

class IncomeExpenseStatics
{
    /**
     * @return string[]
     */
    public static function totalIncomeExpenseValidation(): array
    {
        return [
            "account_type" => "required|in:income,expense",
            "start_date"   => "required|date_format:Y-m-d",
            "end_date"     => "required|date_format:Y-m-d",
        ];
    }

    /**
     * @return string[]
     */
    public static function incomeExpenseEntryValidation(): array
    {
        return [
            'amount' => 'required|numeric',
            'from_account_key' => 'required',
            'to_account_key' => 'required',
            'date' => 'required|date_format:Y-m-d H:i:s',
            'amount_cleared' => 'sometimes|required|numeric'
        ];
    }

    /**
     * @param $account_type
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public static function createDataForAccountsTotal($account_type, $start_date, $end_date): array
    {
        $data['account_type'] = $account_type;
        $data['start_date']   = $start_date." 00:00:00";
        $data['end_date']     = $end_date." 23:59:59";
        return $data;
    }
}