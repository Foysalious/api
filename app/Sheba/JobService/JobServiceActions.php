<?php namespace Sheba\JobService;

use App\Models\Job;
use Sheba\Dal\JobService\JobService;
use Sheba\Dal\JobUpdateLog\JobUpdateLog;
use Illuminate\Support\Facades\DB;
use Sheba\Jobs\Discount;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use Illuminate\Http\Request;

class JobServiceActions
{
    use ModificationFields;

    public function __construct()
    {
        $this->setModifier(request('manager_resource'));
    }

    public function add()
    {
    }

    public function update(JobService $job_service, Request $request, Job $old_job)
    {
        try {
            $job_service_old = clone $job_service;

            $data = $this->getJobServicePriceUpdateData($job_service, $request);
            $data = $this->withUpdateModificationField($data);

            DB::transaction(function () use ($job_service, $request, $job_service_old, $old_job, $data) {
                $job_service->update($data);
                $this->saveServicePriceUpdateLog($job_service_old, $request);
                $job = $job_service->job->calculate(true);
                $this->updateJobDiscount($job);

                try {
                    notify()->customer($job->partnerOrder->order->customer)
                        ->send($this->priceChangedNotificationData($job, $old_job->grossPrice));
                } catch (\Throwable $exception) {

                }
            });

            return ['code' => 200];
        } catch (\Throwable $exception) {
            app('sentry')->captureException($exception);
            return ['code' => 500];
        }
    }

    private function getJobServicePriceUpdateData(JobService $job_service, Request $request)
    {
        $data = [
            'quantity' => (float)$request->quantity,
            'unit_price' => (float)$request->unit_price
        ];
//        if ($request->has('discount') && (float)$request->discount != $job_service->discount) $data['discount'] = (float)$request->discount;
        $this->getNewDiscount($job_service, $data);
        return $data;
    }

    private function getNewDiscount(JobService $job_service, &$data)
    {
        if (isset($data['discount']) && $data['discount'] != 0) {
            $data['discount_percentage'] = '0.00';
            $sheba_contribution_bdt = (float)$job_service->discount * ((float)$job_service->sheba_contribution / 100);
            $data['sheba_contribution'] = (float)number_format($sheba_contribution_bdt / $data['discount'] * 100, 2);
            $data['partner_contribution'] = 100 - $data['sheba_contribution'];
        } elseif (isset($data['discount']) && $data['discount'] == 0) {
            $data['discount_percentage'] = '0.00';
            $data['partner_contribution'] = 0;
            $data['sheba_contribution'] = 0;

        } elseif ($job_service->discount_percentage && $job_service->discount_percentage != "0.00") {
            $data['discount'] = ($data['unit_price'] * $data['quantity'] * $job_service->discount_percentage) / 100;
        }
    }

    private function saveServicePriceUpdateLog(JobService $job_service, Request $request)
    {
        $updated_data = [
            'msg' => 'Service Price Updated',
            'old_service_unit_price' => $job_service->unit_price,
            'old_service_quantity' => $job_service->quantity,
            'new_service_unit_price' => $request->unit_price,
            'new_service_quantity' => $request->quantity,
            'service_name' => $job_service->name
        ];
        $this->jobUpdateLog($job_service->job->id, json_encode($updated_data));
    }

    private function priceChangedNotificationData(Job $job, $old_job_price)
    {
        $order = $job->partnerOrder->order;
        return [
            "title" => "Order: " . $order->code() . " Price changed. Old Price: " . $old_job_price . ", New Price: " . $job->grossPrice,
            "link" => env('SHEBA_FRONT_END_URL') . "/orders/" . $order->id,
            "type" => notificationType('Info'),
            "event_type" => 'App\Models\Order',
            "event_id" => $order->id
        ];
    }

    public function delete(JobService $job_service)
    {
        DB::beginTransaction();
        try {
            $job = $job_service->job;
            if (!$this->isDeletable($job)) return ['code' => 400, 'msg' => "You can't delete this service"];
            $old_job_service = clone $job_service;

            $job_service->delete();
            $this->jobServiceLog($old_job_service, "$job_service->name Deleted");
            $this->updateJobDiscount($job->fresh());
        } catch (\Exception $exception) {
            DB::rollback();
            app('sentry')->captureException($exception);
            return ['code' => 500];
        }
        DB::commit();
        return ['code' => 200];
    }

    private function jobServiceLog(JobService $job_service, $msg)
    {
        $data = $job_service->toArray();
        $data['msg'] = $msg;
        unset($data['job']);

        $this->jobUpdateLog($job_service->job->id, json_encode($data));
    }

    private function jobUpdateLog($job_id, $log)
    {
        $log_data = [
            'job_id' => $job_id,
            'log'    => $log
        ];
        JobUpdateLog::create($this->withCreateModificationField((new RequestIdentification())->set($log_data)));
    }

    private function updateJobDiscount(Job $job)
    {
        $discount = (new Discount())->get($job);
        $job->update($this->withUpdateModificationField(['discount' => $discount]));
    }

    private function isDeletable(Job $job)
    {
        return (!$job->isCancelRequestPending() && !in_array($job->status, ["Served", "Cancelled"]) && $job->jobServices->count() > 1);
    }
}
