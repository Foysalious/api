<?php namespace Sheba\ResourceScheduler;

use App\Models\Category;
use App\Models\Job;
use App\Models\Resource;
use App\Models\ResourceSchedule as Schedule;
use Carbon\Carbon;
use Sheba\Repositories\ResourceScheduleRepository;

class ResourceHandler
{
    /** @var ResourceScheduleRepository */
    private $resourceSchedules;
    /** @var Resource */
    private $resource;

    /**
     * Handler constructor.
     * @param ResourceScheduleRepository $resource_schedules
     * @param Resource $resource
     */
    public function __construct(ResourceScheduleRepository $resource_schedules, Resource $resource)
    {
        $this->resourceSchedules = $resource_schedules;
        $this->resource = $resource;
    }

    /**
     * @param $date
     * @param $time
     * @return mixed
     */
    public function isAvailable($date, $time)
    {
        $date_time = Carbon::parse($date . ' ' . $time);
        return $this->resourceSchedules->filterByDateTime($this->resource, $date_time)->count() == 0 &&
            $this->resourceSchedules->filterStartAt($this->resource, $date_time)->count() == 0;
    }

    /**
     * @param $date
     * @param $time
     * @param Category $category
     * @return mixed
     */
    public function isAvailableForCategory($date, $time, Category $category)
    {
        $start_time = Carbon::parse($date . ' ' . $time);
        $end_time = Carbon::parse($date . ' ' . $time)->addMinutes($category->book_resource_minutes);
        $schedules_start_between = $this->resourceSchedules->filterStartBetween($this->resource, $start_time, $end_time);
        $schedules_end_between = $this->resourceSchedules->filterEndBetween($this->resource, $start_time, $end_time);
        $schedules_at_start = $this->resourceSchedules->filterByDateTime($this->resource, $start_time);
        $schedules_at_end = $this->resourceSchedules->filterByDateTime($this->resource, $end_time);
        $schedules_at_start_end = $this->resourceSchedules->filterStartAndEndAt($this->resource, $start_time, $end_time);
        #dump($start_time, $end_time, $schedules_start_between, $schedules_end_between, $schedules_at_start, $schedules_at_end);
        return $schedules_start_between->count() == 0
            && $schedules_end_between->count() == 0
            && $schedules_at_start->count() == 0
            && $schedules_at_end->count() == 0
            && $schedules_at_start_end->count() == 0;
    }

    /**
     * @param Job $job
     */
    public function book(Job $job)
    {
        $category = $this->getJobCategory($job);
        if(!$category->book_resource_minutes) return;

        $this->resourceSchedules->saveAgainstJob($job, $this->getBookingData($job));
    }

    /**
     * @param Job $job
     */
    public function release(Job $job)
    {
        $schedule = $job->resourceSchedule;
        if($schedule && $schedule->end->lt(Carbon::now())) {
            $this->resourceSchedules->update($schedule, ['end' => Carbon::now()]);
        }
    }

    /**
     * @param Job $job
     */
    public function reAssign(Job $job)
    {
        $category = $this->getJobCategory($job);
        if(!$category->book_resource_minutes) return;

        $this->resourceSchedules->updateAgainstJob($job, $this->getBookingData($job));
    }

    /**
     * @param Schedule $schedule
     * @param $min
     * @return bool
     */
    public function extend(Schedule $schedule, $min)
    {
        $extended_time = $schedule->end->addMinutes($min);
        $conflicted_schedule = $this->resourceSchedules->filterByDateTime($this->resource, $extended_time);
        $is_available_for_extend = $conflicted_schedule->count() == 0;

        if (!$is_available_for_extend) {
            $conflicted_schedule->first()->job->update(['resource_id' => null]);
            $this->resourceSchedules->destroy($conflicted_schedule->first());
        }

        return $this->resourceSchedules->updateAgainstJob($schedule->job, [
            'end' => $extended_time,
            'notify_at' => $this->getNotificationTime($extended_time, $this->getJobCategory($schedule->job))
        ]);
    }

    private function getTimesFromJob(Job $job)
    {
        $category = $this->getJobCategory($job);
        $start = Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start);
        $end = $start->copy()->addMinutes($category->book_resource_minutes);

        return [
            'start'     => $start,
            'end'       => $end,
            'notify_at' => $this->getNotificationTime($end, $category)
        ];
    }

    private function getBookingData(Job $job)
    {
        return $this->getTimesFromJob($job) + ['resource_id' => $this->resource->id];
    }

    private function getJobCategory(Job $job)
    {
        return $job->category_id ? $job->category: $job->service->category;
    }

    private function getNotificationTime(Carbon $end, Category $category)
    {
        return $end->copy()->subMinutes($category->notification_before_min);
    }
}