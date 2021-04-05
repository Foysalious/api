<?php namespace App\Http\Controllers;

use App\Models\Job;
use Exception;
use Illuminate\Support\Collection;
use Sheba\Dal\JobUpdateLog\JobUpdateLog;
use App\Models\Resource;
use App\Repositories\PartnerOrderRepository;
use App\Repositories\ResourceJobRepository;
use Sheba\Jobs\JobTime;
use App\Sheba\UserRequestInformation;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Sheba\Jobs\StatusChanger;
use Sheba\Logistics\DTO\Order;
use Sheba\Logistics\OrderManager;
use Sheba\ModificationFields;
use Sheba\PushNotificationHandler;
use Sheba\Resource\Jobs\Service\ServiceUpdateRequest;
use Sheba\UserAgentInformation;
use Throwable;

class PartnerJobController extends Controller
{
    use ModificationFields;

    /** @var ResourceJobRepository $resourceJobRepository */
    private $resourceJobRepository;
    /** @var mixed $jobStatuses */
    private $jobStatuses;
    /** @var StatusChanger $jobStatusChanger */
    private $jobStatusChanger;

    public function __construct(StatusChanger $job_status_changer)
    {
        $this->resourceJobRepository = new ResourceJobRepository();
        $this->jobStatuses = constants('JOB_STATUSES');
        $this->jobStatusChanger = $job_status_changer;
    }

    public function index($partner, Request $request)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 660);

        $this->validate($request, ['filter' => 'required|string|in:new,ongoing,history']);
        $filter = $request->filter;
        $partner = $request->partner->load([
            'partnerOrders' => function ($q) use ($filter, $request) {
                $q->$filter()->with([
                    'order' => function ($q) {
                        $q->with(['location', 'customer.profile', 'deliveryAddress' => function ($q) {
                            $q->select('id', 'address');
                        }]);
                    }
                ])->with([
                    'jobs' => function ($q) use ($filter, $request) {
                        $q->info()->whereIn('status', (new PartnerOrderRepository())->getStatusFromRequest($request))->orderBy('id', 'desc')->with([
                            'cancelRequests', 'category', 'usedMaterials' => function ($q) {
                                $q->select('id', 'job_id', 'material_name', 'material_price');
                            }, 'resource.profile', 'review'
                        ]);
                    }
                ]);
            }
        ]);

        $jobs = collect();
        $jobs_with_resource = collect();
        foreach ($partner->partnerOrders as $partnerOrder) {
            foreach ($partnerOrder->jobs as $job) {
                /** @var Job $job */
                if ($job->isLogisticCreated()) {
                    $job['logistic_id'] = $job->getCurrentLogisticOrderId();
                    // $job['logistic'] = $job->getCurrentLogisticOrder()->formatForPartner();
                }
                if ($job->cancelRequests->where('status', 'Pending')->count() > 0) continue;
                $job['location'] = $partnerOrder->order->location ? $partnerOrder->order->location->name : $partnerOrder->order->deliveryAddress->address;
                $job['service_unit_price'] = (double)$job->service_unit_price;
                $job['discount'] = (double)$job->discount;
                $job['code'] = $partnerOrder->order->code();
                $job['category_name'] = $job->category ? $job->category->name : null;
                $job['customer_name'] = $partnerOrder->order->customer ? $partnerOrder->order->customer->profile->name : null;
                $job['resource_picture'] = $job->resource != null ? $job->resource->profile->pro_pic : null;
                $job['resource_mobile'] = $job->resource != null ? $job->resource->profile->mobile : null;
                $job['resource_name'] = $job->resource != null ? $job->resource->profile->name : '';
                $job['schedule_timestamp'] = $job->preferred_time_start ? Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start)->timestamp : Carbon::parse($job->schedule_date)->timestamp;
                $job['preferred_time'] = humanReadableShebaTime($job->preferred_time);
                $job['rating'] = $job->review != null ? $job->review->rating : null;
                $job['version'] = $partnerOrder->order->getVersion();

                if ($partnerOrder->closed_and_paid_at != null) {
                    $job['completed_at_timestamp'] = $partnerOrder->closed_and_paid_at->timestamp;
                    $job['closed_and_paid_at'] = $partnerOrder->closed_and_paid_at->format('jS F');
                } else {
                    $job['completed_at_timestamp'] = null;
                    $job['closed_and_paid_at'] = null;
                }
                $job['is_cancel_request_rejected'] = 0;
                if ($job->cancelRequests->count() > 0) {
                    if ($job->cancelRequests->last()->status == constants('CANCEL_REQUEST_STATUSES')['Disapproved']) {
                        $job['is_cancel_request_rejected'] = 1;
                    }
                }
                $job['is_on_premise'] = $job->site == 'partner' ? 1 : 0;
                removeRelationsFromModel($job);
                if ($job->resource_id == null) {
                    $jobs_with_resource->push($job);
                } else {
                    $jobs->push($job);
                }
            }
        }

        $logistic_ids = array_filter($jobs->pluck('logistic_id')->toArray());
        if (!empty($logistic_ids)) $this->attachLogisticOrder($logistic_ids, $jobs);

        if (count($jobs) == 0 && count($jobs_with_resource) == 0) return api_response($request, null, 404);

        if ($filter == 'ongoing') {
            $group_by_jobs = $jobs->groupBy('schedule_date')->sortBy(function ($item, $key) {
                return $key;
            });
            $final = collect();
            foreach ($group_by_jobs as $key => $jobs) {
                $jobs = $jobs->sortBy('schedule_timestamp');
                foreach ($jobs as $job) {
                    $final->push($job);
                }
            }
            $jobs = $jobs_with_resource->merge($final);
        } else {
            $jobs = $jobs_with_resource->merge($jobs);
            $jobs = $jobs->sortByDesc('id');
        }
        list($offset, $limit) = calculatePagination($request);
        $jobs = $jobs->splice($offset, $limit);
        $resources = collect();
        foreach ($jobs->groupBy('resource_id') as $key => $resource) {
            if (!empty($key)) {
                $resources->push(array(
                    'id' => (int)$key,
                    'name' => $resource->first()->resource_name
                ));
            }
        }
        $subscription_orders = $partner->subscriptionOrders->where('status', 'accepted')->count();

        return api_response($request, $jobs, 200, ['jobs' => $jobs, 'resources' => $resources, 'subscription_orders' => $subscription_orders]);
    }

    public function acceptJobAndAssignResource($partner, $job, Request $request)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 660);
        $this->validate($request, [
            'resource_id' => 'int'
        ]);

        $this->jobStatusChanger->acceptJobAndAssignResource($request);
        if ($this->jobStatusChanger->hasError()) {
            return api_response($request, null, $this->jobStatusChanger->getErrorCode(), [
                'message' => $this->jobStatusChanger->getErrorMessage()
            ]);
        }

        return api_response($request, $this->jobStatusChanger->getChangedJob(), 200);
    }

    public function declineJob($partner, $job, Request $request)
    {
        $this->jobStatusChanger->decline($request);
        if ($this->jobStatusChanger->hasError()) {
            return api_response($request, null, $this->jobStatusChanger->getErrorCode(), [
                'message' => $this->jobStatusChanger->getErrorMessage()
            ]);
        }
        return api_response($request, null, 200);
    }

    public function update($partner, $job, Request $request)
    {
        /** @var Job $job */
        $job = $request->job;
        $statuses = 'start,end';
        foreach (constants('JOB_STATUSES') as $key => $value) {
            $statuses .= ",$value";
        }
        $this->validate($request, [
            'schedule_date' => 'sometimes|required|date|after:' . Carbon::yesterday(),
            'preferred_time' => 'required_with:schedule_date|string',
            'resource_id' => 'string',
            'status' => 'sometimes|required|in:' . $statuses
        ]);
        if ($request->has('schedule_date') && $request->has('preferred_time')) {
            $job_time = new JobTime($request->day, $request->time);
            $job_time->validate();
            if (!$job_time->isValid) {
                return api_response($request, null, 400, ['message' => $job_time->error_message]);
            }
            if ($job->hasResource() && !scheduler(Resource::find((int)$job->resource_id))->isAvailableForCategory($request->schedule_date, explode('-', $request->preferred_time)[0], $job->category, $job)) {
                return api_response($request, null, 403, ['message' => 'Resource is not available at this time. Please select different date time or change the resource']);
            }
            $request->merge(['resource' => $request->manager_resource]);
            $response = $this->resourceJobRepository->reschedule($job->id, $request);
            return $response ? api_response($request, $response, $response->code) : api_response($request, null, 500);
        }
        if ($request->has('resource_id')) {
            if ((int)$job->resource_id == (int)$request->resource_id) return api_response($request, null, 403, ['message' => 'অর্ডারটিতে এই রিসোর্স এসাইন করা রয়েছে']);
            if (!scheduler(Resource::find((int)$request->resource_id))->isAvailableForCategory($job->schedule_date, explode('-', $job->preferred_time)[0], $job->category, $job)) {
                return api_response($request, null, 403, ['message' => 'Resource is not available at this time. Please select different date time or change the resource']);
            }
            if ($request->partner->hasThisResource((int)$request->resource_id, 'Handyman') && $job->hasStatus(['Accepted', 'Schedule_Due', 'Process', 'Serve_Due'])) {
                $job = $this->assignResource($job, $request->resource_id, $request->manager_resource);
                return api_response($request, $job, 200);
            }
            return api_response($request, null, 403);
        }
        if ($request->has('status')) {
            $new_status = $request->status;
            if ($new_status === 'start') $new_status = $this->jobStatuses['Process'];
            elseif ($new_status === 'end') $new_status = $this->jobStatuses['Served'];
            if ($response = (new \Sheba\Repositories\ResourceJobRepository($request->manager_resource))->changeJobStatus($job, $new_status)) {
                return api_response($request, $response, $response->code, ['message' => $response->msg]);
            }
        }
        return api_response($request, null, 500);
    }

    public function getMaterials($partner, $job, Request $request)
    {
        $materials = $request->job->usedMaterials;
        if (count($materials) == 0) return api_response($request, $job, 404);

        $materials->each(function ($item, $key) {
            $item['material_price'] = (double)$item->material_price;
            $item['added_by'] = $item->created_by_name;
            removeSelectedFieldsFromModel($item);
        });
        return api_response($request, $materials, 200, ['materials' => $materials, 'total_material_price' => $materials->sum('material_price')]);

    }

    public function addMaterial($partner, $job, Request $request, UserAgentInformation $user_agent_information, ServiceUpdateRequest $update_request)
    {
        $this->validate($request, ['name' => 'required|string', 'price' => 'required|numeric|min:1']);
        $this->setModifier($request->manager_resource);
        $user_agent_information->setRequest($request);
        $response = $update_request->setJob($request->job)->setUserAgentInformation($user_agent_information)
            ->setMaterials([['name' => $request->name, 'price' => (double)$request->price]])->update();
        return api_response($request, 1, $response->getCode(), ['message' => $response->getMessage()]);
    }

    public function updateMaterial($partner, $job, Request $request, ServiceUpdateRequest $update_request, UserAgentInformation $user_agent_information)
    {
        $this->validate($request, [
            'material_id' => 'required|numeric',
            'name' => 'required|string',
            'price' => 'required|numeric|min:1'
        ]);
        $this->setModifier($request->manager_resource);
        $user_agent_information->setRequest($request);
        $job = $request->job;
        $material = $job->usedMaterials->where('id', (int)$request->material_id)->first();
        $response = $update_request->setJob($job)->setUserAgentInformation($user_agent_information)->updateMaterial($material, $request->name, $request->price);
        return api_response($request, 1, $response->getCode(), ['message' => $response->getMessage()]);
    }

    private function jobUpdateLog($job_id, $log, $created_by)
    {
        $logData = [
            'job_id' => $job_id,
            'log' => $log,
            'created_by' => $created_by->id,
            'created_by_name' => class_basename($created_by) . "-" . $created_by->profile->name,
            'created_by_type' => 'App\\Models\\' . class_basename($created_by)
        ];
        JobUpdateLog::create(array_merge((new UserRequestInformation(\request()))->getInformationArray(), $logData));
    }

    private function assignResource(Job $job, $resource_id, Resource $manager_resource)
    {
        $old_resource = $job->resource_id;
        $new_resource = ( int)$resource_id;
        $updatedData = [
            'msg' => 'Resource Change',
            'old_resource_id' => $old_resource,
            'new_resource_id' => $new_resource
        ];

        $job->resource_id = $resource_id;
        $job->update();
        if (empty($old_resource)) {
            scheduler($job->resource)->book($job);
        } else {
            scheduler(Resource::find($old_resource))->release($job);
            scheduler($job->resource)->reAssign($job);
        }
        $this->jobUpdateLog($job->id, json_encode($updatedData), $manager_resource);

        try {
            $this->sendAssignResourcePushNotifications($job);
        } catch (Throwable $e) {
            logError($e);
        }

        return $job;
    }

    private function sendAssignResourcePushNotifications(Job $job)
    {
        $topic = config('sheba.push_notification_topic_name.customer') . $job->partner_order->order->customer->id;
        $channel = config('sheba.push_notification_channel_name.customer');
        (new PushNotificationHandler())->send([
            "title" => 'Resource has been assigned',
            "message" => $job->resource->profile->name . " has been added as a resource for your job.",
            "event_type" => 'Job',
            "event_id" => $job->id,
            "sound" => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel);

        $topic = config('sheba.push_notification_topic_name.resource') . $job->resource_id;
        $channel = config('sheba.push_notification_channel_name.resource');
        (new PushNotificationHandler())->send([
            "title" => 'Assigned to a new job',
            "message" => 'You have been assigned to a new job. Job ID: ' . $job->partnerOrder->order->code(),
            "event_type" => 'PartnerOrder',
            "event_id" => $job->partnerOrder->id,
            "sound" => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel);
    }

    public function cancelRequests($partner, Request $request)
    {
        $jobs = Job::whereHas('cancelRequests', function ($query) {
            $query->where('status', 'Pending');
        })->whereHas('partnerOrder', function ($q) use ($partner) {
            $q->ongoing()->where('partner_id', $partner);
        })->with(['category' => function ($q) {
            $q->select('id', 'name');
        }, 'partnerOrder' => function ($q) {
            $q->with(['order' => function ($q) {
                $q->select('id', 'customer_id', 'location_id', 'sales_channel')->with(['location' => function ($q) {
                    $q->select('id', 'name');
                }, 'customer' => function ($q) {
                    $q->select('id', 'profile_id')->with(['profile' => function ($q) {
                        $q->select('id', 'name');
                    }]);
                }]);
            }]);
        }])->info()->orderBy('jobs.id', 'desc')->get();
        foreach ($jobs as $job) {
            $job['is_on_premise'] = (int)$job->isOnPremise();
            $job['location'] = $job->partnerOrder->order->location->name;
            $job['code'] = $job->partnerOrder->order->code();
            $job['category_name'] = $job->category ? $job->category->name : null;
            $job['customer_name'] = $job->partnerOrder->order->customer ? $job->partnerOrder->order->customer->profile->name : null;
            $job['schedule_timestamp'] = $job->partnerOrder->getVersion() == 'v2' ? Carbon::parse($job->schedule_date . ' ' . explode('-', $job->preferred_time)[0])->timestamp : Carbon::parse($job->schedule_date)->timestamp;
            $job['preferred_time'] = humanReadableShebaTime($job->preferred_time);
            $job['version'] = $job->partnerOrder->order->getVersion();
            removeRelationsFromModel($job);
        }
        return count($jobs) == 0 ? api_response($request, null, 404) : api_response($request, $jobs, 200, ['jobs' => $jobs]);
    }

    /**
     * @param array $logistic_ids
     * @param Collection $jobs
     * @throws Exception
     */
    private function attachLogisticOrder(array $logistic_ids, Collection $jobs)
    {
        $imploded_logistic_ids = implode(',', $logistic_ids);
        /** @var OrderManager $logistic_order_manager */
        $logistic_order_manager = app(OrderManager::class);
        $orders = $logistic_order_manager->getMinimals($imploded_logistic_ids);

        $logistic_orders = [];
        foreach ($orders as $data) {
            $order = new Order();
            $order->setStatus($data['status'])->setRider($data['rider'])->setId($data['id']);
            $logistic_orders[$order->id] = $order->formatForPartner();
        }
        $jobs->whereIn('logistic_id', $logistic_ids)->each(function ($job) use ($logistic_orders) {
            $job['logistic'] = $logistic_orders[$job->logistic_id] ?? null;
        });
    }
}
