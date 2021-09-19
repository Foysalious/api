<?php namespace App\Http\Controllers;

use App\Models\Job;
use Sheba\Dal\JobService\JobService;
use App\Repositories\JobServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\JobService\JobServiceActions;
use Sheba\ModificationFields;

class JobServiceController extends Controller
{
    use ModificationFields;
    private $jobServiceRepository;

    public function __construct()
    {
        $this->jobServiceRepository = new JobServiceRepository();
    }

    public function update($partner, $job_service, Request $request)
    {
        try {
            $job_service = JobService::find($job_service);
            $invalid_job_statuses = [constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Declined'], constants('JOB_STATUSES')['Not_Responded'], constants('JOB_STATUSES')['Served'], constants('JOB_STATUSES')['Cancelled']];
            $old_job = clone $job_service->job;
            $old_job = $old_job->calculate(true);

            $request->merge(['status' => $old_job->status]);
            $this->validate($request, [
                'status' => 'required|not_in:' . implode(',', $invalid_job_statuses),
                'quantity' => 'required|numeric|min:1',
                'unit_price' => 'required|numeric|min:0.01',
//                'discount' => 'numeric|min:0.00'
            ], ['status.not_in' => $old_job->status . ' job cannot be updated']);
            if ($error = $this->hasPricingError($job_service, $old_job, $request)) {
                return api_response($request, null, 400, ['message' => $error]);
            }
            if ($old_job->isCancelRequestPending()) {
                return api_response($request, null, 400, ['message' => 'Unprocessable job. Cancel request pending.']);
            }

            $this->setModifier($request->manager_resource);
            $response = (new JobServiceActions)->update($job_service, $request, $old_job);

            if ($response['code'] == 200) {
//                if ($request->has('discount')) $message = "$request->discount Tk have been successfully discounted.";
//                else
                $message = "Job service updated successfully";

                return api_response($request, null, 200, ['message' => $message]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $exception) {
            app('sentry')->captureException($exception);
            return api_response($request, null, 500);
        }
    }

    private function hasPricingError(JobService $job_service, Job $job, Request $request)
    {
        if (floatval($job->discount) > ($request->quantity * $request->unit_price)) {
            return "Service Total Price can't be smaller than discount.";
        }
//        if ($request->has('discount')) {
//            if (($job_service->discount != $request->discount) && $job->serviceDiscountContributionSheba > (float)$request->discount) {
//                return "Service Discount can't be smaller than " . $job->serviceDiscountContributionSheba;
//            }
//            if ($job->totalPrice < $job->discount - $job_service->discount + $request->discount) {
//                return "Service Discount can't be greater than total price";
//            }
//            if ($job->partner_order->calculate(true)->due < (floatval($request->discount) - floatval($job_service->discount))) {
//                return "Discount can't be greater than due";
//            }
//            if ($job->partner_order->order->voucher_id) {
//                return "Discount can't be added because a Promo is already Applied";
//            }
//        }
    }

    public function destroy($partner, $job_service, Request $request)
    {
        try {
            $job_service = JobService::find($job_service);
            if (!$job_service) return api_response($request, null, 404, ['message' => "Service not found"]);
            $response = (new JobServiceActions)->delete($job_service);
            if ($response['code'] == 400) return api_response($request, null, 400, ['message' => $response['msg']]);
            elseif ($response['code'] == 200) return api_response($request, null, 200);
            else return api_response($request, null, 500);
        } catch (\Throwable $exception) {
            app('sentry')->captureException($exception);
            return api_response($request, null, 500);
        }
    }
}
