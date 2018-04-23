<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\PartnerService;
use App\Repositories\JobServiceRepository;
use App\Sheba\Checkout\PartnerList;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobServiceController extends Controller
{
    private $jobServiceRepository;

    public function __construct()
    {
        $this->jobServiceRepository = new JobServiceRepository();
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

}