<?php namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobService;
use App\Models\JobUpdateLog;
use App\Repositories\JobServiceRepository;
use App\Sheba\Checkout\PartnerList;
use App\Sheba\UserRequestInformation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class JobServiceController extends Controller
{
    use ModificationFields;
    private $jobServiceRepository;

    private $modifier_id;
    private $time;
    private $modifier_name;

    public function __construct()
    {
        $this->jobServiceRepository = new JobServiceRepository();
    }

    private function setModifier($modifier)
    {
        $this->modifier_id = $modifier->id;
        $this->modifier_name = substr(strrchr(get_class($modifier), '\\'), 1). ' - ' . $modifier->profile->name;
        $this->time = Carbon::now();
    }

    public function store($partner,Request $request)
    {
        try {
            $this->validate($request, [
                'services' => 'required|string',
                'remember_token' => 'required|string',
            ]);
            $partner_order = $request->partner_order;
            if ($partner_order->jobs->count() > 1) {
                $job = $partner_order->jobs->whereIn('status', array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Not_Responded'],
                    constants('JOB_STATUSES')['Schedule_Due'], constants('JOB_STATUSES')['Process']))->first();
            } else {
                $job = $partner_order->jobs->first();
            }
            $partner_list = new PartnerList(json_decode($request->services), $job->schedule_date, $job->preferred_time_start . '-' . $job->preferred_time_end, $job->partnerOrder->order->location);
//            if ($job_service = $this->jobServiceRepository->save($partner_service, $data)) {
//                return api_response($request, $job_service, 200);
//            }
            return api_response($request, null, 500);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function update($partner, $job_service, Request $request)
    {
        $job_service = JobService::find($job_service);
        try {
            $invalid_job_statuses = [constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Declined'], constants('JOB_STATUSES')['Not_Responded'], constants('JOB_STATUSES')['Served'], constants('JOB_STATUSES')['Cancelled']];
            $job_service_old = clone $job_service;
            $old_job = clone $job_service_old->job;
            $old_job = $old_job->calculate(true);

            $request->merge(['status' => $old_job->status]);
            $this->validate($request, [
                'status'        => 'required|not_in:'.implode(',', $invalid_job_statuses),
                'quantity'      => 'required|numeric|min:1',
                'unit_price'    => 'required|numeric|min:0.01',
                'discount'      => 'numeric|min:0.01'
            ], ['status.not_in' => $old_job->status . ' job cannot be updated']);
            if ($error = $this->hasPricingError($job_service, $old_job, $request)) {
                return api_response($request, null, 400, ['message' => $error]);
            }
            if ($old_job->isCancelRequestPending()) {
                return api_response($request, null, 400, ['message' => 'Unprocessable job. Cancel request pending.']);
            }
            $data = $this->getJobServicePriceUpdateData($job_service, $request);
            array_merge($data, [
                'updated_by'        => $this->modifier_id,
                'updated_by_name'   => $this->modifier_name,
                'updated_at'        => $this->time
            ]);
            $this->setModifier($request->manager_resource);

            DB::transaction(function () use ($job_service, $request, $job_service_old, $old_job, $data) {
                $job_service->update($data);
                $this->saveServicePriceUpdateLog($job_service_old, $request);
                $job = $job_service->job;
                $job = $job->calculate(true);
                notify()->customer($job->partnerOrder->order->customer)
                    ->send($this->priceChangedNotificationData($job, $old_job->grossPrice));
            });

            if ($request->has('discount')) $message = "$request->discount Tk have been successfully discounted.";
            else $message = "Job service updated successfully";

            return api_response($request, null, 200, ['message' => $message]);
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $exception){
            app('sentry')->captureException($exception);
            return api_response($request, null, 500);
        }
    }

    private function hasPricingError(JobService $job_service, Job $job, Request $request)
    {
        http_response_code(500);

        if (floatval($job->discount) > ($request->quantity * $request->unit_price)) {
            return "Service Total Price can't be smaller than discount.";
        }
        if ($request->has('discount')) {
            if (($job_service->discount != $request->discount) && $job->serviceDiscountContributionSheba > (float)$request->discount) {
                return "Service Discount can't be smaller than ". $job->serviceDiscountContributionSheba;
            }
            if ($job->totalPrice < $job->discount - $job_service->discount + $request->discount) {
                return "Service Discount can't be greater than total price";
            }
            if ($job->partner_order->calculate(true)->due < (float) $request->discount) {
                return "Discount can't be greater than due";
            }
        }
    }

    private function getJobServicePriceUpdateData(JobService $job_service, Request $request)
    {
        $data = [
            'quantity'   => (float)$request->quantity,
            'unit_price' => (float)$request->unit_price
        ];
        if ($request->has('discount') && (float)$request->discount != $job_service->discount) $data['discount'] = (float)$request->discount;
        $this->getNewDiscount($job_service, $data);
        return $data;
    }

    private function getNewDiscount(JobService $job_service, &$data)
    {
        if (isset($data['discount'])) {
            $data['discount_percentage'] = '0.00';
            $sheba_contribution_bdt = (float)$job_service->discount * ((float)$job_service->sheba_contribution/100);
            $data['sheba_contribution'] = (float) number_format($sheba_contribution_bdt/$data['discount']*100, 2);
            $data['partner_contribution'] = 100 - $data['sheba_contribution'];
        } elseif ($job_service->discount_percentage && $job_service->discount_percentage != "0.00") {
            $data['discount'] = ($data['unit_price'] * $data['quantity'] * $job_service->discount_percentage) / 100;
        }
    }

    private function saveServicePriceUpdateLog(JobService $job_service, Request $request)
    {
        $updated_data = [
            'msg' => 'Service Price Updated',
            'old_service_unit_price' => $job_service->unit_price,
            'old_service_quantity'   => $job_service->quantity,
            'new_service_unit_price' => $request->unit_price,
            'new_service_quantity'   => $request->quantity,
            'service_name'           => $job_service->name
        ];
        $this->jobUpdateLog($job_service->job->id, json_encode($updated_data));
    }

    private function jobUpdateLog($job_id, $log)
    {
        $log_data = [
            'job_id' => $job_id,
            'log'    => $log
        ];
        JobUpdateLog::create(array_merge((new UserRequestInformation(\request()))->getInformationArray(), $log_data, [
            'created_by'        => $this->modifier_id,
            'created_by_name'   => $this->modifier_name,
            'created_at'        => $this->time,
            'updated_by'        => $this->modifier_id,
            'updated_by_name'   => $this->modifier_name,
            'updated_at'        => $this->time,
        ]));
    }

    private function priceChangedNotificationData(Job $job, $old_job_price)
    {
        return [
            "title" => "Job: " . $job->fullCode() . " Price changed. Old Price: " . $old_job_price .", New Price: " . $job->grossPrice,
            "link"  => url("job/" . $job->id),
            "type"  => notificationType('Info'),
            "event_type" => 'App\Models\Job',
            "event_id"   => $job->id
        ];
    }
}