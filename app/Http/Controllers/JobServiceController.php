<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobService;
use App\Models\PartnerService;
use App\Models\Service;
use App\Repositories\DiscountRepository;
use App\Repositories\JobServiceRepository;
use App\Repositories\PartnerServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobServiceController extends Controller
{
    private $discountRepository;
    private $jobServiceRepository;
    private $partnerServiceRepository;

    public function __construct()
    {
        $this->discountRepository = new DiscountRepository();
        $this->jobServiceRepository = new JobServiceRepository();
        $this->partnerServiceRepository = new PartnerServiceRepository();
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'job_id' => 'required|numeric',
                'service_id' => 'required|numeric',
                'quantity' => 'required:min:1',
                'option' => 'required|array',
                'created_by' => 'required|numeric',
                'created_by_name' => 'required|string',
                'additional_info' => 'sometimes|required|string'
            ]);
            $job = Job::find((int)$request->job_id);
            $partner_service = PartnerService::where([['partner_id', $job->partner_order->partner_id], ['service_id', (int)$request->service_id]])->first();
            $data = $request->only(['job_id', 'service_id', 'quantity', 'additional_info', 'created_by', 'created_by_name', 'option']);
            if ($job_service = $this->jobServiceRepository->save($partner_service, $data)) {
                return api_response($request, $job_service, 200);
            }
            return api_response($request, null, 500);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

}