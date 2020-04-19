<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Models\Job;
use GuzzleHttp\Exception\GuzzleException;
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
use Sheba\Resource\Jobs\ServiceUpdateRequest;
use Sheba\Resource\Jobs\Updater\StatusUpdater;
use Sheba\Resource\Schedule\Extend\ExtendTime;
use Sheba\Resource\Service\ServiceList;
use Sheba\UserAgentInformation;

class ResourceJobController extends Controller
{
    use ModificationFields;

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
            return api_response($request, null, 400, ['message' => 'আপনার শিডিউল পরিবর্তন টি সফল হয় নি।অন্য একটি দিন অথবা সময় নির্ধারিত করে পুনরায় চেষ্টা করুন।']);
        } catch (\Throwable $e) {
            return api_response($request, null, 500, ['message' => 'আপনার শিডিউল পরিবর্তন টি সফল হয় নি।অন্য একটি দিন অথবা সময় নির্ধারিত করে পুনরায় চেষ্টা করুন।']);
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
        $collect_money->setResource($resource)->setJob($job)->setUserAgentInformation($user_agent_information)->setCollectionAmount($request->amount);
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
        // TODO: Check Partner Option Availability
        $services = $serviceList->setJob($job)->getServicesList();
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
            $updatedBill = $billUpdate->getUpdatedBillForServiceAdd($job);
            return api_response($request, $updatedBill, 200, ['bill' => $updatedBill]);
        }
        if ($request->has('materials')) {
            $updatedBill = $billUpdate->getUpdatedBillForMaterialAdd($job);
            return api_response($request, $updatedBill, 200, ['bill' => $updatedBill]);
        }
        if ($request->has('quantity')) {
            $updatedBill = $billUpdate->getUpdatedBillForQuantityUpdate($job);
            return api_response($request, $updatedBill, 200, ['bill' => $updatedBill]);
        }
    }

    public function updateService(Job $job, Request $request, ServiceUpdateRequest $updateRequest)
    {
        $this->validate($request, ['services' => 'string', 'quantity' => 'string', 'materials' => 'string']);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $this->setModifier($resource);
        if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job."]);
        $updateRequest->setMaterials(json_decode($request->materials, 1))->setServices(json_decode($request->services, 1))->setJob($job)
            ->setQuantity(json_decode($request->quantity,1))->update();
        return api_response($request, null, 200);
    }
}