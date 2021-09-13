<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Resource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Authentication\AuthUser;
use Sheba\ModificationFields;
use Sheba\Resource\Jobs\BillInfo;
use Sheba\Resource\Jobs\BillUpdate;
use Sheba\Resource\Jobs\Collection\CollectMoney;
use Sheba\Resource\Jobs\JobInfo;
use Sheba\Resource\Jobs\JobList;
use Sheba\Resource\Jobs\Reschedule\Reschedule;
use Sheba\Resource\Jobs\Service\ServiceUpdateRequest;
use Sheba\Resource\Jobs\Updater\StatusUpdater;
use Sheba\Resource\Schedule\Extend\ExtendTime;
use Sheba\Resource\Service\ServiceList;
use Sheba\UserAgentInformation;
use Throwable;

class ResourceJobController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @return Resource
     */
    private function getResource(Request $request)
    {
        $auth_user = $request->auth_user;
        return $auth_user->getResource();
    }

    public function index(Request $request, JobList $job_list)
    {
        $this->validate($request, ['offset' => 'numeric|min:0', 'limit' => 'numeric|min:1']);
        list($offset, $limit) = calculatePagination($request);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $jobs = $job_list->setResource($resource)->getOngoingJobs();
        if (count($jobs) == 0) return api_response($request, $jobs, 404);
        return api_response($request, $jobs, 200, ['orders' => $jobs->splice($offset, $limit)]);
    }

    public function getAllJobs(Request $request, JobList $job_list)
    {
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $upto_todays_jobs = $job_list->setResource($resource)->getOngoingJobs();
        $tomorrows_jobs = $job_list->setResource($resource)->getTomorrowsJobs();
        $rest_jobs = $job_list->setResource($resource)->getRestJobs();
        return api_response($request, $job_list, 200, ['jobs' => [['title' => 'আজকে', 'jobs' => $upto_todays_jobs], ['title' => 'আগামীকালকে', 'jobs' => $tomorrows_jobs], ['title' => 'পরবর্তী', 'jobs' => $rest_jobs]]]);
    }

    public function getJobToShowInHome(Request $request, JobList $job_list)
    {
        $resource = $this->getResource($request);
        $upto_todays_jobs = $job_list->setResource($resource)->getOngoingJobs();
        if (count($upto_todays_jobs) > 0) return api_response($request, $upto_todays_jobs, 200, ['orders' => $upto_todays_jobs->splice(0, 1)]);
        $tomorrows_jobs = $job_list->setResource($resource)->getTomorrowsJobs();
        if (count($tomorrows_jobs) > 0) return api_response($request, $tomorrows_jobs, 200, ['orders' => $tomorrows_jobs->splice(0, 1)]);
        return api_response($request, null, 404);
    }

    public function jobDetails(Job $job, Request $request, JobInfo $jobInfo)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job."]);
        $job = $jobInfo->setResource($resource)->getJobDetails($job);
        return api_response($request, $job, 200, ['job_details' => $job]);
    }

    public function getBills(Job $job, Request $request, BillInfo $billInfo)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job."]);
        $bill = $billInfo->getBill($job);
        return api_response($request, $bill, 200, ['bill' => $bill]);
    }

    public function getNextJob(Request $request, JobList $job_list)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $job = $job_list->setResource($resource)->getNextJob();
        if (!$job) return api_response($request, $job, 404);
        return api_response($request, $job, 200, ['job' => $job]);
    }

    public function updateStatus(Job $job, Request $request, StatusUpdater $status_updater, UserAgentInformation $user_agent_information)
    {
        $this->validate($request, ['status' => 'string|in:process,served']);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job."]);
        $user_agent_information->setRequest($request);
        $status_updater->setResource($resource)->setJob($job)->setUserAgentInformation($user_agent_information)->setStatus($request->status);
        $response = $status_updater->update();
        return api_response($request, $response, $response->getCode(), ['message' => $response->getMessage()]);
    }

    public function rescheduleJob(Job $job, Request $request, Reschedule $reschedule_job, UserAgentInformation $user_agent_information)
    {
        try {
            $this->validate($request, ['schedule_date' => 'string', 'schedule_time_slot' => 'string']);
            /** @var AuthUser $auth_user */
            $auth_user = $request->auth_user;
            $resource = $auth_user->getResource();
            if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job."]);
            $user_agent_information->setRequest($request);
            $reschedule_job->setResource($resource)->setJob($job)->setUserAgentInformation($user_agent_information)->setScheduleDate($request->schedule_date)
                ->setScheduleTimeSlot($request->schedule_time_slot);
            $response = $reschedule_job->reschedule();
            return api_response($request, $response, $response->getCode(), ['message' => $response->getMessage()]);
        } catch (ValidationException $e) {
            throw new Exception('আপনার এই প্রক্রিয়া টি সম্পন্ন করা সম্ভব নয়, অনুগ্রহ করে একটু পরে আবার চেষ্টা করুন', 500);
        } catch (Throwable $e) {
            throw new Exception('আপনার এই প্রক্রিয়া টি সম্পন্ন করা সম্ভব নয়, অনুগ্রহ করে একটু পরে আবার চেষ্টা করুন', 500);
        }
    }

    public function collectMoney(Job $job, Request $request, CollectMoney $collect_money, UserAgentInformation $user_agent_information)
    {
        $this->validate($request, ['amount' => 'required|numeric']);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job's bill."]);
        $user_agent_information->setRequest($request);
        $collect_money->setResource($resource)->setPartnerOrder($job->partnerOrder)->setUserAgentInformation($user_agent_information)->setCollectionAmount($request->amount);
        $response = $collect_money->collect();
        return api_response($request, $response, $response->getCode(), ['message' => $response->getMessage()]);
    }

    public function extendTime(Job $job, Request $request, ExtendTime $extend_time)
    {
        $this->validate($request, ['time_in_minutes' => 'required|numeric']);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job."]);
        $response = $extend_time->setJob($job)->setExtendedTimeInMinutes($request->time_in_minutes)->extend();
        return api_response($request, $response, $response->getCode(), ['message' => $response->getMessage()]);
    }

    public function getServices(Job $job, Request $request, ServiceList $serviceList)
    {
        $services = $serviceList->setJob($job)->setRequest($request)->getServicesList();
        return api_response($request, null, 200, ['services' => $services]);
    }

    public function getUpdatedBill(Job $job, BillUpdate $billUpdate, Request $request)
    {
        $this->validate($request, [
            'services' => 'sometimes|required|string',
            'materials' => 'sometimes|required|string',
            'quantity' => 'sometimes|required|string',
        ]);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job."]);

        if ($request->has('services')) {
            if (!is_array(json_decode($request->services))) return api_response($request, null, 400);
            $updatedBill = $billUpdate->getUpdatedBillForServiceAdd($job);
            return api_response($request, $updatedBill, 200, ['bill' => $updatedBill]);
        }
        if ($request->has('materials')) {
            if (!is_array(json_decode($request->materials))) return api_response($request, null, 400);
            $updatedBill = $billUpdate->getUpdatedBillForMaterialAdd($job);
            return api_response($request, $updatedBill, 200, ['bill' => $updatedBill]);
        }
        if ($request->has('quantity')) {
            if (!is_array(json_decode($request->quantity))) return api_response($request, null, 400);
            $updatedBill = $billUpdate->getUpdatedBillForQuantityUpdate($job);
            return api_response($request, $updatedBill, 200, ['bill' => $updatedBill]);
        }
    }

    public function updateService(Job $job, Request $request, ServiceUpdateRequest $updateRequest, UserAgentInformation $user_agent_information)
    {
        try {
            $this->validate($request, ['services' => 'string', 'quantity' => 'string', 'materials' => 'string']);
            /** @var AuthUser $auth_user */
            $auth_user = $request->auth_user;
            $resource = $auth_user->getResource();
            if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job."]);

            $this->setModifier($resource);
            $user_agent_information->setRequest($request);

            $services = json_decode($request->services, 1) ?: [];
            $quantity = json_decode($request->quantity, 1) ?: [];
            $materials = json_decode($request->materials, 1) ?: [];
            if (count($services) > 0) $updateRequest->setServices($services);
            if (count($materials) > 0) $updateRequest->setMaterials($materials);
            if (count($quantity) > 0) $updateRequest->setQuantity($quantity);
            $response = $updateRequest->setJob($job)->setUserAgentInformation($user_agent_information)->update();
            return api_response($request, null, $response->getCode(), ['message' => $response->getMessage()]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500, ['message' => 'আপনার এই প্রক্রিয়া টি সম্পন্ন করা সম্ভব নয়, অনুগ্রহ করে একটু পরে আবার চেষ্টা করুন']);
        }
    }

    public function getAllHistoryJobs(Request $request, JobList $job_list)
    {
        $this->validate($request, [
            'offset' => 'numeric|min:0', 'limit' => 'numeric|min:1',
            'month' => 'sometimes|required|integer|between:1,12', 'year' => 'sometimes|required|integer'
        ]);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $jobs = $job_list->setResource($resource);
        if ($request->has('limit')) $jobs = $jobs->setOffset($request->offset)->setLimit($request->limit);
        if ($request->has('year')) $jobs = $jobs->setYear($request->year);
        if ($request->has('month')) $jobs = $jobs->setMonth($request->month);
        $jobs = $jobs->getHistoryJobs();
        return api_response($request, $jobs, 200, ['jobs' => ['years' => $jobs]]);
    }

    public function jobSearch(Request $request, JobList $job_list)
    {
        $this->validate($request, ['q' => 'required']);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if (substr($request->q, 1, 1) == '-') {
            $order_id = (int)substr($request->q, 2) - config('sheba.order_code_start');
            $results = $job_list->setResource($resource)->setOrderId($order_id)->getJobsFilteredByOrderId();
            $order_code = $request->q;
            $jobs = $results->filter(function ($job) use ($order_code) {
                return $job['order_code'] == $order_code;
            });
        } else {
            $jobs = $job_list->setResource($resource)->setQuery('"' . $request->q . '"');
            if ($request->has('limit')) $jobs = $jobs->setOffset($request->offset)->setLimit($request->limit);
            $jobs = $jobs->getJobsFilteredByServiceOrCustomerName();
        }
        if ($jobs->isEmpty()) return api_response($request, $jobs, 404);
        return api_response($request, $jobs, 200, ['results' => $jobs]);
    }

    public function getNextJobsInfo(Request $request, JobList $jobList)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $next_jobs_info = $jobList->setResource($resource)->getNextJobsInfo();
        if(!$next_jobs_info) return api_response($request, $next_jobs_info, 404, ['message' => 'No multiple jobs found']);
        return api_response($request, $next_jobs_info, 200, ['next_jobs_info' => $next_jobs_info]);
    }
}
