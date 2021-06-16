<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use App\Sheba\TopUp\Vendor\Vendors;

class TopUpDataFormat
{
    /**
     * @param $topups
     * @return array[]
     */
    public function topUpHistoryDataFormat($topups)
    {
        $topup_data = [];
        $topup_data_for_excel = [];
        foreach ($topups as $topup) {
            /** @var TopUpOrder $topup */
            $payee_mobile = $topup->payee_mobile;
            $payee_name = $topup->payee_name ? $topup->payee_name : 'N/A';
            $amount = $topup->amount;
            $operator = $topup->vendor->name;
            $payee_mobile_type = $topup->payee_mobile_type;
            $status = $topup->getStatusForAgent();
            $failed_reason = (new TopUpFailedReason())->setTopup($topup)->getFailedReason();
            $created_at = $topup->created_at->format('jS M, Y h:i A');
            $created_date = $topup->created_at->format('jS M, Y');
            $created_time = $topup->created_at->format('h:i A');
            $created_at_raw = $topup->created_at->format('Y-m-d h:i:s');

            array_push($topup_data, $this->topUpData($payee_mobile, $payee_name, $amount, $operator, $payee_mobile_type, $status, $failed_reason, $created_at, $created_date, $created_time,  $created_at_raw));
            array_push($topup_data_for_excel, $this->topUpExcelData($payee_mobile, $operator, $payee_mobile_type, $amount, $status, $payee_name, $created_at_raw));
        }
        return [$topup_data, $topup_data_for_excel];
    }

    /**
     * @param $payee_mobile
     * @param $payee_name
     * @param $amount
     * @param $operator
     * @param $payee_mobile_type
     * @param $status
     * @param $failed_reason
     * @param $created_at
     * @param $created_date
     * @param $created_time
     * @param $created_at_raw
     * @return array
     */
    private function topUpData($payee_mobile, $payee_name, $amount, $operator, $payee_mobile_type, $status, $failed_reason, $created_at,  $created_date, $created_time, $created_at_raw)
    {
        return [
            'payee_mobile' => $payee_mobile,
            'payee_name' => $payee_name,
            'amount' => $amount,
            'operator' => $operator,
            'payee_mobile_type' => $payee_mobile_type,
            'status' => $status,
            'failed_reason' => $failed_reason,
            'created_at' => $created_at,
            'created_date' => $created_date,
            'created_time' => $created_time,
            'created_at_raw' => $created_at_raw
        ];
    }

    /**
     * @param $payee_mobile
     * @param $operator
     * @param $payee_mobile_type
     * @param $amount
     * @param $status
     * @param $payee_name
     * @param $created_at_raw
     * @return array
     */
    private function topUpExcelData($payee_mobile, $operator, $payee_mobile_type, $amount, $status, $payee_name, $created_at_raw)
    {
        return [
            'mobile' => $payee_mobile,
            'operator' => $operator == Vendors::GRAMEENPHONE ? "GP" : $operator,
            'connection_type' => $payee_mobile_type,
            'amount' => $amount,
            'status' => $status,
            'name' => $payee_name,
            'created_date' => $created_at_raw
        ];
    }

    /**
     * @param $topups
     * @return array
     */
    public function allTopUpDataFormat($topups)
    {
        $topup_data = [];
        foreach ($topups as $topup) {
            /** @var TopUpOrder $topup */
            $payee_mobile = $topup->payee_mobile;
            $operator = $topup->vendor->name;
            $operator_waiting_time = $topup->vendor->waiting_time;
            $payee_mobile_type = $topup->payee_mobile_type;
            $created_at_raw = $topup->created_at->format('Y-m-d h:i:s');

            array_push($topup_data, $this->allTopUpData($payee_mobile, $operator, $operator_waiting_time, $payee_mobile_type, $created_at_raw));
        }

        return $topup_data;
    }

    /**
     * @param $payee_mobile
     * @param $operator
     * @param $operator_waiting_time
     * @param $payee_mobile_type
     * @param $created_at_raw
     * @return array
     */
    private function allTopUpData($payee_mobile, $operator, $operator_waiting_time, $payee_mobile_type, $created_at_raw)
    {
        return [
            'payee_mobile' => $payee_mobile,
            'operator' => $operator,
            'waiting_time' => $operator_waiting_time,
            'payee_mobile_type' => $payee_mobile_type,
            'created_at_raw' => $created_at_raw
        ];
    }
}
