<?php namespace Sheba\TopUp;

use App\Models\Partner;
use App\Models\TopUpOrder;
use App\Models\TopUpVendorCommission;
use App\Sheba\TopUp\Vendor\Vendors;
use Sheba\Dal\SubscriptionWisePaymentGateway\Model as SubscriptionWisePaymentGateway;

class TopUpDataFormat
{
    private $error_message = "Currently, we’re supporting ";

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
            $created_at_raw = $topup->created_at->format('Y-m-d H:i:s');

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

    /**
     * @param $vendor
     * @param $agent
     * @param $topup_charges
     */
    public function makeVendorWiseCommissionData(&$vendor, $agent, $topup_charges)
    {
        $vendor_commission = TopUpVendorCommission::where([['topup_vendor_id', $vendor->id], ['type', $agent]])->first();
        $asset_name = strtolower(trim(preg_replace('/\s+/', '_', $vendor->name)));
        array_add($vendor, 'asset', $asset_name);
        if ($agent === "App\Models\Partner") {
            foreach ($topup_charges as $charge)
                if (strtolower($charge->key) == strtolower($vendor->name))
                    $subscription_wise_commission = $charge;
            if (!isset($subscription_wise_commission)) {
                array_add($vendor, 'agent_commission', $vendor_commission ? $vendor_commission->agent_commission : 0);
            } else {
                array_add($vendor, 'agent_commission', $subscription_wise_commission->commission);
            }
        } else
            array_add($vendor, 'agent_commission', $vendor_commission ? $vendor_commission->agent_commission : 0);

        array_add($vendor, 'is_prepaid_available', 1);
        array_add($vendor, 'is_postpaid_available', ($vendor->id != 6) ? 1 : 0);
        if ($vendor->is_published) $this->error_message .= $vendor->name.", ";
    }

    public function getAdditionalData(): array
    {
        return array_merge(self::getRegularExpression(), ["error_message" => rtrim($this->error_message, ", ")]);
    }

    public static function getRegularExpression(): array
    {
        return [
            'typing' => "^(013|13|014|14|018|18|016|16|017|17|019|19|015|15)",
            'from_contact' => "^(?:\+?88)?01[16|8]\d{8}$"
        ];
    }

    /**
     * @param $vendor_id
     * @param $agent
     * @param $partner
     * @param $vendor_name
     * @param $otf_settings
     * @param $otf_list
     */
    public function makeTopUpOTFData($vendor_id, $agent, $partner, $vendor_name, $otf_settings, &$otf_list)
    {
        $vendor_commission = TopUpVendorCommission::where([['topup_vendor_id', $vendor_id], ['type', $agent]])->first();
        if ($agent === "App\Models\Partner")
            $topup_charges = (new TopUpChargesSubscriptionWise())->getCharges($partner);

        if(isset($topup_charges))
            $single_charge = (new TopUpChargesSubscriptionWise())->getChargeByVendor($topup_charges, $vendor_name);

        $vendor_agent_commission = isset($single_charge) ? $single_charge->commission : $vendor_commission->agent_commission;
        $vendor_otf_commission   = isset($single_charge) ? $single_charge->otf_commission : $otf_settings->agent_commission;

        foreach ($otf_list as $otf) {
            array_add($otf, 'regular_commission', round(min(($vendor_agent_commission / 100) * $otf->amount, 50), 2));
            array_add($otf, 'otf_commission', round(($vendor_otf_commission / 100) * $otf->cashback_amount, 2));
        }
    }
}
