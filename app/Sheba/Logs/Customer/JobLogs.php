<?php namespace Sheba\Logs\Customer;

use App\Models\Job;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Support\Str;

class JobLogs
{
    private $job;
    private $generalLogs;
    private $scheduleChangeLogs;
    private $priceChangeLogs;
    private $materialChangeLogs;

    public function __construct(Job $job)
    {
        $this->job = $job;
        $this->generalLogs = collect([]);
        $this->priceChangeLogs = collect([]);
        $this->scheduleChangeLogs = collect([]);
        $this->statusChangeLogs = collect([]);
        $this->materialChangeLogs = collect([]);
        $this->orderStatusLogs = collect([]);
        $this->comments = collect([]);
    }

    public function getorderStatusLogs()
    {
        $partner = $this->job->partnerOrder->partner;
        $resource = $this->job->resource;
        $job_status = $this->job->status;
        $logs = [];
        if (constants('JOB_STATUS_SEQUENCE')[$job_status] > 0) {
            array_push($logs, array(
                'status' => 'order_placed',
                'log' => 'Order has been placed to ' . $partner->name . '.',
                'user' => array(
                    'name' => $partner->name,
                    'picture' => $partner->logo,
                    'mobile' => $partner->getManagerMobile(),
                    'type' => 'partner',
                )
            ));
        }
        if (constants('JOB_STATUS_SEQUENCE')[$job_status] > 1) {
            array_push($logs, [
                'status' => 'order_confirmed',
                'log' => 'Order has been confirmed.',
            ]);
            if ($resource) {
                array_push($logs, [
                    'status' => 'expert_assigned',
                    'log' => 'An expert has been assigned to your order.',
                    'user' => array(
                        'name' => $resource->profile->name,
                        'picture' => $resource->profile->pro_pic,
                        'mobile' => $resource->profile->mobile,
                        'type' => 'resource',
                    )
                ]);
            }
        }
        if ($work_log = $this->formatWorkLog()) array_push($logs, $work_log);
        if ($message_log = $this->getOrderMessage()) array_push($logs, $message_log);
        return $logs;
    }

    private function formatWorkLog()
    {
        $job_status = $this->job->status;
        if (in_array($job_status, [constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Serve_Due'], constants('JOB_STATUSES')['Served']])) {
            $status_change_log = $this->job->statusChangeLogs->where('to_status', $job_status)->first();
            if (!$status_change_log) return null;
            $time = $status_change_log->created_at->format('h:i A, M d');
            if (in_array($job_status, [constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Serve_Due']])) {
                return [
                    'status' => 'work_started',
                    'log' => 'Expert has started working from ' . $time
                ];
            } elseif ($job_status == constants('JOB_STATUSES')['Served']) {
                return [
                    'status' => 'work_completed',
                    'log' => 'Expert has completed your order at ' . $time,
                ];
            }
        } else {
            return null;
        }
    }

    public function getOrderMessage()
    {
        $job_status = $this->job->status;
        $job_review = $this->job->review;
        if (in_array($job_status, [constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Not_Responded']])) {
            return array(
                'status' => 'message',
                'log' => 'Your order is awaiting for confirmation. Please, contact the service provider.',
                'type' => 'danger'
            );
        } elseif ($job_status == constants('JOB_STATUSES')['Accepted']) {
            $thirty_min_before_scheduled_date_time = Carbon::parse($this->job->schedule_date . ' ' . $this->job->preferred_time_start)->subMinutes(30);
            if (Carbon::now()->gte($thirty_min_before_scheduled_date_time)) {
                return array(
                    'status' => 'message',
                    'log' => 'Your order is supposed to be started within ' . humanReadableShebaTime($this->job->preferred_time_start) . '.' . ($this->job->resource ? 'Please call ' . $this->job->resource->profile->name . ' to confirm.' : ''),
                    'type' => 'success'
                );
            } else {
                return array(
                    'status' => 'message',
                    'log' => 'Expert will arrive at your place between ' . humanReadableShebaTime($this->job->preferred_time) . ', ' . Carbon::parse($this->job->schedule_date)->format('M d'),
                    'type' => 'success'
                );
            }
        } elseif ($job_status == constants('JOB_STATUSES')['Schedule_Due']) {
            return array(
                'status' => 'message',
                'log' => 'Your order is supposed to be started by now. Please call the ' . ($this->job->resource ? 'expert' : 'service provider') . '. For any kind of help call 16516.',
                'type' => 'danger'
            );
        } elseif (in_array($job_status, [constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Serve_Due']])) {
            return array(
                'status' => 'message',
                'log' => 'Expert is working on your order.',
                'type' => 'success'
            );
        } elseif ($job_status == constants('JOB_STATUSES')['Served'] && !$job_review) {
            return array(
                'status' => 'message',
                'log' => 'Please rate the expert based on your service experience.',
                'type' => 'success'
            );
        } else {
            return null;
        }
    }

    public function all()
    {
        foreach ($this->job->updateLogs as $update_log) {
            $log = json_decode($update_log->log, 1);
            if ($this->isScheduleChangeLog($log)) {
                $this->newScheduleChangeLog($update_log, $log);
            } else if ($this->isPriceChangeLog($log)) {
                $this->newPriceChangeLog($update_log, $log);
            } else {
                $this->generalLog($update_log, $log);
            }
        }
        $this->materialLogs($this->job->materialLogs);
        $this->statusChangeLogs($this->job->statusChangeLogs);
        $this->getComments($this->job->comments->load('accessors')->filter(function ($comment) {
            return $comment->accessors->pluck('model_name')->contains('App\\Models\\Customer');
        }));

        return [
            'general' => $this->generalLogs,
            'schedule_change' => $this->scheduleChangeLogs,
            'price_change' => $this->formatLogInPriceChangeLogs($this->priceChangeLogs),
            'status_change' => $this->statusChangeLogs,
            'comments' => $this->comments,
            'complains' => $this->formatComplainLogs($this->job->customerComplains())
        ];
    }

    private function formatLogInPriceChangeLogs($priceChangeLogs)
    {
        return $priceChangeLogs->each(function ($item, $key) {
            $item->log = $item->log . ' from ' . $item->from . ' to ' . $item->to;
        });
    }

    private function formatComplainLogs($complains)
    {
        $collection = collect();
        foreach ($complains as $complain) {
            $logs = $complain->logs;
            foreach ($logs as $log) {
                if ($log->field == 'status') {
                    if ($log->to == 'Observation') {
                        $temp = " against Order is in ";
                    } else {
                        $temp = " against Order is ";
                    }
                    $collection->push((object)[
                        'log' => 'Your Complain ' . $complain->code() . $temp . $log->to . '.',
                        'created_at' => $log->created_at
                    ]);
                }
            }

        }
        return $collection;
    }

    private function getComments($comments)
    {
        foreach ($comments as $comment) {
            $commentator = Str::contains($comment->created_by_name, '-') ? explode('-', $comment->created_by_name)[1] : $comment->created_by_name;
            $this->comments->push((object)[
                'created_at' => $comment->created_at,
                'created_by_name' => $comment->created_by_name,
                'comment' => $comment->comment,
                'log' => "$commentator has commented - $comment->comment",
            ]);
        }
    }

    private function statusChangeLogs($status_changes)
    {
        foreach ($status_changes->unique('to_status') as $status_change) {
            if (in_array($status_change->to_status, ['Declined', 'Schedule Due', 'Not Responded', 'Serve Due'])) continue;
            if (in_array($status_change->to_status, ['Declined', 'Accepted'])) {
                //$log = 'Your Order has been ' . $status_change->to_status . ' by ' . explode('-', $status_change->created_by_name)[1] . ".";
                $log = 'Your Order has been ' . $status_change->to_status;
            } elseif ($status_change->to_status == "Schedule Due") {
                $log = 'Your Order Status has been changed from ' . $status_change->from_status . ' to ' . $status_change->to_status . ".";
            } elseif ($status_change->to_status == "Process") {
                $log = 'Order is In Process.';
            } elseif ($status_change->to_status == "Served") {
                $log = 'Order has been Served Successfully.';
            } elseif ($status_change->to_status == "Cancelled") {
                $log = 'Order is cancelled.';
            } else {
                $log = 'Your Order status has been changed from ' . $status_change->from_status . ' to ' . $status_change->to_status . ' by ' . $status_change->created_by_name . ".";
            }

            $this->statusChangeLogs->push((object)[
                'created_at' => $status_change->created_at,
                'created_by_name' => $status_change->created_by_name,
                'log' => $log
            ]);
        }
    }

    private function materialLogs($materialLogs)
    {
        foreach ($materialLogs as $materialLog) {
            $this->materialChangeLogs->push((object)[
                'created_at' => $materialLog->created_at,
                'created_by_name' => $materialLog->created_by_name,
                'log' => $this->getMaterialLog($materialLog)
            ]);
        }
    }

    private function isNewMaterialAddChangeLog($materialLog)
    {
        return $materialLog->old_data == null && $materialLog->new_data != null;
    }

    private function isMaterialUpdateChangeLog($materialLog)
    {
        return $materialLog->old_data != null && $materialLog->new_data != null;
    }

    private function getMaterialUpdatedFields($old_data, $new_data)
    {
        $price_update = false;
        $name_update = false;
        if (trim($new_data->material_price) != trim($old_data->material_price)) {
            $price_update = true;
        }
        if (trim($new_data->material_name) != trim($old_data->material_name)) {
            $name_update = true;
        }
        return array($name_update, $price_update);
    }

    private function generalLog($update_log, $decoded_log)
    {
        if ($this->isResourceChangeLog($decoded_log)) {
            $this->newResourceChangeLog($update_log, $decoded_log);
        } else if ($this->isAdditionalInfoChangeLog($decoded_log)) {
            $this->newAdditionalInfoChangeLog($update_log, $decoded_log);
        } else if ($this->isCMChangeLog($decoded_log)) {
//            $this->newCMChangeLog($update_log, $decoded_log);
        } else if ($this->isPartnerChangeLog($decoded_log)) {
            $this->newPartnerChangeLog($update_log, $decoded_log);
        }
    }

    private function newResourceChangeLog($update_log, $decoded_log)
    {
        if ($decoded_log['old_resource_id'] == null) {
            $resource = Resource::find((int)$decoded_log['new_resource_id']);
            $log = ($resource ? $resource->profile->name : '(Deleted Resource)') . " has been assigned to serve the order.";
        } else {
            $log = (Resource::find((int)$decoded_log['new_resource_id']))->profile->name . " has been reassigned to serve the order instead of " . (Resource::find((int)$decoded_log['old_resource_id']))->profile->name;
        }
        $this->generalLogs->push((object)[
            "log" => $log,
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
            "log" => "Additional Info updated",
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newPartnerChangeLog($update_log, $decoded_log)
    {
        $this->generalLogs->push((object)[
            "log" => $decoded_log['msg'] . ".",
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newScheduleChangeLog($update_log, $decoded_log)
    {
        try {
            $this->scheduleChangeLogs->push((object)[
                "log" => "Your Order Schedule has been changed from " . (Carbon::parse(array_values($decoded_log)[1]))->format('jS F, Y') . " " . array_values($decoded_log)[3] .
                    " to " . (Carbon::parse(array_values($decoded_log)[0]))->format('jS F, Y') . " " . array_values($decoded_log)[2] . ".",
                "created_at" => $update_log->created_at,
                "created_by_name" => $update_log->created_by_name
            ]);
        } catch ( \Throwable $e ) {

        }
    }

    private function newPriceChangeLog($update_log, $decoded_log)
    {
        if ($decoded_log['msg'] == "Service Price Updated") {
            if ((double)$decoded_log['old_service_unit_price'] != (double)$decoded_log['new_service_unit_price']) {
                $this->newUnitPriceChangeLog($update_log, $decoded_log);
            }
            if ((double)$decoded_log['old_service_quantity'] != (double)$decoded_log['new_service_quantity']) {
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
            "log" => "Service Unit Price Updated",
            "from" => $decoded_log['old_service_unit_price'],
            "to" => $decoded_log['new_service_unit_price'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newQuantityChangeLog($update_log, $decoded_log)
    {
        $this->priceChangeLogs->push((object)[
            "log" => "Your Order Service " . $decoded_log['service_name'] . " quantity changed ",
            "from" => $decoded_log['old_service_quantity'],
            "to" => $decoded_log['new_service_quantity'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newDiscountChangeLog($update_log, $decoded_log)
    {
        $this->priceChangeLogs->push((object)[
            "log" => "Your Order Discount has been changed ",
            "from" => (double)$decoded_log['old_discount_cost'],
            "to" => (double)$decoded_log['new_discount_cost'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newCommissionChangeLog($update_log, $decoded_log)
    {
        $this->priceChangeLogs->push((object)[
            "log" => "Commission Rate Updated.",
            "from" => $decoded_log['old_commission_rate'],
            "to" => $decoded_log['new_commission_rate'],
            "created_at" => $update_log->created_at,
            "created_by_name" => $update_log->created_by_name
        ]);
    }

    private function newVatChangeLog($update_log, $decoded_log)
    {
        $this->priceChangeLogs->push((object)[
            "log" => "VAT Updated.",
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
            (in_array($log['msg'], ["Service Price Updated", "Discount Cost Updated", "Commission Rate Updated"]));
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

    /**
     * @param $materialLog
     * @return string
     */
    private function getMaterialLog($materialLog)
    {
        if ($this->isNewMaterialAddChangeLog($materialLog)) {
            $log = trim($materialLog->new_data->material_name) . ' material has been added for ' . trim($materialLog->new_data->material_price) . ' TK';
        } elseif ($this->isMaterialUpdateChangeLog($materialLog)) {
            list($name_update, $price_update) = $this->getMaterialUpdatedFields($materialLog->old_data, $materialLog->new_data);
            if ($name_update && $price_update) {
                $log = trim($materialLog->old_data->material_name) . ' material of ' . trim($materialLog->old_data->material_price) . ' TK' . ' has been updated to ' . trim($materialLog->new_data->material_name) . ' material of ' . trim($materialLog->new_data->material_price) . ' TK';
            } elseif ($name_update) {
                $log = trim($materialLog->old_data->material_name) . ' material name has been updated to ' . trim($materialLog->new_data->material_name);
            } else {
                $log = trim($materialLog->old_data->material_name) . ' material price has been updated to ' . trim($materialLog->new_data->material_price) . ' TK from ' . trim($materialLog->old_data->material_price) . ' TK';
            }
        } else {
            $log = trim($materialLog->old_data->material_name) . ' material of ' . trim($materialLog->old_data->material_price) . ' TK has been deleted';
        }
        return $log;
    }
}