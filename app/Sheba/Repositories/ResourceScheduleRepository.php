<?php namespace Sheba\Repositories;

use App\Models\Job;
use App\Models\Resource;
use App\Models\ResourceSchedule;
use App\Models\ResourceScheduleLog;
use Carbon\Carbon;

class ResourceScheduleRepository extends BaseRepository
{
    private $resourceSchedule;
    private $log;

    public function __construct(ResourceSchedule $resource_schedule, ResourceScheduleLog $log)
    {
        $this->resourceSchedule = $resource_schedule;
        $this->log = $log;
    }

    public function getAllForResource(Resource $resource)
    {
        return $this->resourceSchedule->where('resource_id', $resource->id)->get();
    }

    public function filterByDateTime(Resource $resource, Carbon $date_time)
    {
        return $this->resourceSchedule->where('start', '<=', $date_time)
            ->where('end', '>=', $date_time)
            ->where('resource_id', $resource->id)
            ->get();

    }

    public function saveAgainstJob(Job $job, $data)
    {
        $this->save($data + ['job_id' => $job->id]);
    }

    public function save($data)
    {
        $this->resourceSchedule->create($this->withBothModificationFields($data));
    }

    public function updateAgainstJob(Job $job, $data)
    {
        return $this->update($job->resourceSchedule, $data);
    }

    public function update(ResourceSchedule $resource_schedule, $data)
    {
        return $resource_schedule->update($this->withUpdateModificationField($data));
    }
}