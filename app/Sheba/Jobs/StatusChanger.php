<?php namespace Sheba\Jobs;

use App\Models\Job;
use App\Models\JobUpdateLog;
use App\Models\Resource;
use App\Repositories\ResourceJobRepository;
use App\Sheba\UserRequestInformation;
use Illuminate\Http\Request;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\PushNotificationHandler;
use Sheba\Resource\ResourceTypes;
use Throwable;

class StatusChanger
{
    use HasErrorCodeAndMessage;

    /** @var ResourceJobRepository */
    private $resourceJobRepo;
    /** @var Job */
    private $changedJob = null;

    public function __construct(ResourceJobRepository $resource_job_repo)
    {
        $this->resourceJobRepo = $resource_job_repo;
    }

    /**
     * @return Job
     */
    public function getChangedJob()
    {
        return $this->changedJob;
    }

    public function checkForError(Request $request)
    {
        $job = $request->job;
        if (!$request->partner->hasThisResource((int)$request->resource_id, ResourceTypes::HANDYMAN)) {
            $this->setError(403, "Resource doesn't work for you");
            return;
        }

        if (!$job->isAcceptable()) {
            $this->setError(403, $job->status . " job cannot be accepted.");
            return;
        }
    }

    /**
     * @param Request $request
     * @return void
     */
    public function acceptJobAndAssignResource(Request $request)
    {
        $this->checkForError($request);
        if ($this->hasError()) return;

        $job = $request->job;
        $this->changeStatus($job, $request, JobStatuses::ACCEPTED);
        if ($this->hasError()) return;
        $this->changedJob = $this->assignResource($job, $request->resource_id, $request->manager_resource);
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
            scheduler($job->resource)->reAssign($job);
        }
        $this->jobUpdateLog($job->id, json_encode($updatedData), $manager_resource);

        try {
            $this->sendAssignResourcePushNotifications($job);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
        }

        return $job;
    }

    /**
     * @param Job $job
     */
    private function sendAssignResourcePushNotifications(Job $job)
    {
        $topic   = config('sheba.push_notification_topic_name.customer') . $job->partner_order->order->customer->id;
        $channel = config('sheba.push_notification_channel_name.customer');
        (new PushNotificationHandler())->send([
            "title" => 'Resource has been assigned',
            "message" => $job->resource->profile->name . " has been added as a resource for your job.",
            "event_type" => 'Job',
            "event_id" => $job->id,
            "sound" => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel);

        $topic   = config('sheba.push_notification_topic_name.resource') . $job->resource_id;
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

    private function jobUpdateLog($job_id, $log, $created_by)
    {
        JobUpdateLog::create(array_merge((new UserRequestInformation(\request()))->getInformationArray(), [
            'job_id' => $job_id,
            'log' => $log,
            'created_by' => $created_by->id,
            'created_by_name' => class_basename($created_by) . "-" . $created_by->profile->name,
            'created_by_type' => 'App\\Models\\' . class_basename($created_by)
        ]));
    }

    private function changeStatus(Job $job, Request $request, $status)
    {
        $request->merge([
            'remember_token' => $request->manager_resource->remember_token,
            'status' => $status,
            'resource' => $request->manager_resource
        ]);

        $response = $this->resourceJobRepo->changeStatus($job->id, $request);
        if (!$response) {
            $this->setError(500);
            return;
        }
        if ($response->code != 200)  {
            $this->setError($response->code, $response->msg);
            return;
        }
    }

    public function decline(Request $request)
    {
        $this->changeStatus($request->job, $request, JobStatuses::DECLINED);
    }
}
