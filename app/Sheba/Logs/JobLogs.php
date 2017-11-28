<?php namespace Sheba\Logs;

use App\Models\Job;
use App\Models\Resource;

class JobLogs
{
    private $job;
    private $generalLogs;
    private $scheduleChangeLogs;
    private $priceChangeLogs;

    public function __construct(Job $job)
    {
        $this->job = $job;

        $this->generalLogs = collect([]);
        $this->priceChangeLogs = collect([]);
        $this->scheduleChangeLogs = collect([]);
    }

    public function all()
    {
        foreach($this->job->updateLogs as $update_log) {
            $log = json_decode($update_log->log, 1);
            if($this->isScheduleChangeLog($log)) {
                $this->newScheduleChangeLog($update_log, $log);
            } else if ($this->isPriceChangeLog($log)) {
                $this->newPriceChangeLog($update_log, $log);
            } else {
                $this->generalLog($update_log, $log);
            }
        }

        return [
            'general' => $this->generalLogs,
            'schedule_change' => $this->scheduleChangeLogs,
            'price_change' => $this->priceChangeLogs,
            'status_change' => $this->job->statusChangeLog
        ];
    }

    private function generalLog($update_log, $decoded_log)
    {
        if($this->isResourceChangeLog($decoded_log)) {
            $this->newResourceChangeLog($update_log, $decoded_log);
        } else if ($this->isAdditionalInfoChangeLog($decoded_log)) {
            $this->newAdditionalInfoChangeLog($update_log, $decoded_log);
        } else if ($this->isCMChangeLog($decoded_log)) {
            $this->newCMChangeLog($update_log, $decoded_log);
        } else if ($this->isPartnerChangeLog($decoded_log)) {
            $this->newPartnerChangeLog($update_log, $decoded_log);
        }
    }

    private function newResourceChangeLog($update_log, $decoded_log)
    {
        $resource = Resource::find((int)$decoded_log['new_resource_id']);
        $this->generalLogs->push((object)[
            "log" => ($resource ? $resource->name : '(Deleted Resource)') . " was assigned as Resource.",
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newCMChangeLog($update_log, $decoded_log)
    {
        $this->generalLogs->push((object)[
            "log" => "Something was updated from others section.",
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newAdditionalInfoChangeLog($update_log, $decoded_log)
    {
        $this->generalLogs->push((object)[
            "log" =>  "Additional Info updated",
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newPartnerChangeLog($update_log, $decoded_log)
    {
        $this->generalLogs->push((object)[
            "log" => $decoded_log['msg'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newScheduleChangeLog($update_log, $decoded_log)
    {
        $field = ucwords(str_replace('_', ' ', array_keys($decoded_log)[0]));
        $value = array_values($decoded_log)[0];
        $this->scheduleChangeLogs->push((object)[
            "log" =>  $field . " was set to " . $value,
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newPriceChangeLog($update_log, $decoded_log)
    {
        if($decoded_log['msg'] == "Service Price Updated ") {
            if($decoded_log['old_service_unit_price'] != $decoded_log['new_service_unit_price']) {
                $this->newUnitPriceChangeLog($update_log, $decoded_log);
            }

            if($decoded_log['old_service_quantity'] != $decoded_log['new_service_quantity']) {
                $this->newQuantityChangeLog($update_log, $decoded_log);
            }
        } else if ($decoded_log['msg'] == "Discount Cost Updated") {
            $this->newDiscountChangeLog($update_log, $decoded_log);
        } else if ($decoded_log['msg'] == "Commission Rate Updated") {
            $this->newCommissionChangeLog($update_log, $decoded_log);
        } else if ($decoded_log['msg'] == "VAT Updated") {
            $this->newVatChangeLog($update_log, $decoded_log);
        }
    }

    private function newUnitPriceChangeLog($update_log, $decoded_log)
    {
        $this->priceChangeLogs->push((object)[
            "log" =>  "Service Unit Price Updated.",
            "from" => $decoded_log['old_service_unit_price'],
            "to" => $decoded_log['new_service_unit_price'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newQuantityChangeLog($update_log, $decoded_log)
    {
        $this->priceChangeLogs->push((object)[
            "log" =>  "Service Quantity Updated.",
            "from" => $decoded_log['old_service_quantity'],
            "to" => $decoded_log['new_service_quantity'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newDiscountChangeLog($update_log, $decoded_log)
    {
        $this->priceChangeLogs->push((object)[
            "log" =>  "Discount Updated.",
            "from" => $decoded_log['old_discount_cost'],
            "to" => $decoded_log['new_discount_cost'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newCommissionChangeLog($update_log, $decoded_log)
    {
        $this->priceChangeLogs->push((object)[
            "log" =>  "Commission Rate Updated.",
            "from" => $decoded_log['old_commission_rate'],
            "to" => $decoded_log['new_commission_rate'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newVatChangeLog($update_log, $decoded_log)
    {
        $this->priceChangeLogs->push((object)[
            "log" =>  "VAT Updated.",
            "from" => $decoded_log['old_vat'],
            "to" => $decoded_log['new_vat'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    /**
     * @param $log
     * @return bool
     */
    private function isScheduleChangeLog($log)
    {
        return array_key_exists('schedule_date', $log) || array_key_exists('preferred_time', $log);
    }

    /**
     * @param $log
     * @return bool
     */
    private function isPriceChangeLog($log)
    {
        return array_key_exists('msg', $log) &&
            ( in_array($log['msg'], ["Service Price Updated ", "Discount Cost Updated", "Commission Rate Updated"]) );
    }

    /**
     * @param $log
     * @return bool
     */
    private function isResourceChangeLog($log)
    {
        return array_key_exists('msg', $log) && $log['msg'] == "Resource Change";
    }

    /**
     * @param $log
     * @return bool
     */
    private function isAdditionalInfoChangeLog($log)
    {
        return array_key_exists('job_additional_info', $log);
    }

    /**
     * @param $log
     * @return bool
     */
    private function isCMChangeLog($log)
    {
        return array_key_exists('crm_id', $log);
    }

    /**
     * @param $log
     * @return bool
     */
    private function isPartnerChangeLog($log)
    {
        return array_key_exists('msg', $log) && startsWith($log['msg'], "Partner Changed to");
    }
}