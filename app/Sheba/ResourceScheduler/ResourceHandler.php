<?php namespace Sheba\ResourceScheduler;

use Sheba\Dal\Category\Category;
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

    private $bookedSchedules;

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
            $this->resourceSchedules->filterStartAt($this->resource, $date_time)->count() == 0 &&
            !$this->resource->runningLeave($date_time);
    }

    /**
     * @param $date
     * @param $time
     * @param Category $category
     * @return mixed
     */
    public function reasonForNotAvailable($date, $time, Category $category)
    {
        $start_time = Carbon::parse($date . ' ' . $time);
        $end_time = Carbon::parse($date . ' ' . $time)->addMinutes($category->book_resource_minutes);

        $schedules_start_between = $this->resourceSchedules->filterStartBetween($this->resource, $start_time, $end_time);
        $schedules_end_between = $this->resourceSchedules->filterEndBetween($this->resource, $start_time, $end_time);
        $schedules_at_start = $this->resourceSchedules->filterByDateTime($this->resource, $start_time);
        $schedules_at_end = $this->resourceSchedules->filterByDateTime($this->resource, $end_time);
        $schedules_at_start_end = $this->resourceSchedules->filterStartAndEndAt($this->resource, $start_time, $end_time);

        return $schedules_start_between->merge($schedules_end_between)->merge($schedules_at_start)->merge($schedules_at_end)->merge($schedules_at_start_end);
    }

    /**
     * @param $date
     * @param $time
     * @param Category $category
     * @param Job|null $job
     * @return bool
     */
    public function isAvailableForCategory($date, $time, Category $category, Job $job = null)
    {
        $start_time = Carbon::parse($date . ' ' . $time);
        $end_time = Carbon::parse($date . ' ' . $time)->addMinutes($category->book_resource_minutes);
        $schedules_start_between = $this->resourceSchedules->filterStartBetween($this->resource, $start_time, $end_time);
        $schedules_end_between = $this->resourceSchedules->filterEndBetween($this->resource, $start_time, $end_time);
        $schedules_at_start = $this->resourceSchedules->filterByDateTime($this->resource, $start_time);
        $schedules_at_end = $this->resourceSchedules->filterByDateTime($this->resource, $end_time);
        $schedules_at_start_end = $this->resourceSchedules->filterStartAndEndAt($this->resource, $start_time, $end_time);

        $this->bookedSchedules = $schedules_start_between->merge($schedules_end_between)
            ->merge($schedules_at_start)
            ->merge($schedules_at_end)
            ->merge($schedules_at_start_end);

        if ($job) {
            $this->bookedSchedules = $this->bookedSchedules->reject(function ($schedule) use ($job) {
                return $schedule->job_id == $job->id;
            });
        }
        return $this->bookedSchedules->count() == 0 && !$this->resource->runningLeave($start_time) && !$this->resource->runningLeave($end_time);
    }

    public function getBookedJobs()
    {
        $jobs = collect();
        foreach ($this->bookedSchedules->load('job') as $schedule) {
            $jobs->push($schedule->job);
        }
        return $jobs;
    }

    /**
     * @param Job $job
     */
    public function book(Job $job)
    {
        $category = $this->getJobCategory($job);
        if (!$category->book_resource_minutes) return;

        $this->resourceSchedules->saveAgainstJob($job, $this->getBookingData($job));
    }

    /**
     * @param Job $job
     */
    public function release(Job $job)
    {
        $schedule = $job->resourceScheduleSlot->where('resource_id', $this->resource->id)->first();
        if ($schedule && $schedule->end->gt(Carbon::now())) {
            $this->resourceSchedules->update($schedule, ['end' => Carbon::now()]);
        }
    }

    /**
     * @param Job $job
     */
    public function reAssign(Job $job)
    {
        $schedule = $job->resourceSchedule;
        if (empty($schedule)) {
            $this->book($job);
            return;
        }

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

    public function reSchedule(Job $job, $reschedule_data)
    {
        $schedule = $job->resourceScheduleSlot->where('resource_id', $this->resource->id)->first();
        if (empty($schedule)) {
            $this->book($job);
            return;
        }

        $category = $this->getJobCategory($job);
        if (!$category->book_resource_minutes) return;

        $end = $reschedule_data['start']->copy()->addMinutes($category->book_resource_minutes);
        $reschedule_data += [
            'end' => $end,
            'notify_at' => $this->getNotificationTime($end, $category)
        ];
        $this->resourceSchedules->update($schedule, $reschedule_data);
    }

    private function getTimesFromJob(Job $job)
    {
        $category = $this->getJobCategory($job);
        $start = Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start);
        $end = $start->copy()->addMinutes($category->book_resource_minutes);

        return [
            'start' => $start,
            'end' => $end,
            'notify_at' => $this->getNotificationTime($end, $category)
        ];
    }

    private function getBookingData(Job $job)
    {
        return $this->getTimesFromJob($job) + ['resource_id' => $this->resource->id];
    }

    private function getJobCategory(Job $job)
    {
        return $job->category_id ? $job->category : $job->service->category;
    }

    private function getNotificationTime(Carbon $end, Category $category)
    {
        return $end->copy()->subMinutes($category->notification_before_min);
    }
}
