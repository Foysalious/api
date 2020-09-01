<?php namespace Sheba\CmDashboard;

use App\Models\Job;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduledJobs
{
    private $user;
    private $today;
    private $tomorrow;

    public function __construct()
    {
        $this->user     = Auth::user();
        $this->today    = Carbon::today();
        $this->tomorrow = Carbon::tomorrow();
    }

    public function get()
    {
        $jobs = $this->makeQuery()->get();
        list($todays_jobs, $tomorrows_jobs) = $this->processData($jobs);

        return $scheduledJobs = [
            'today' => [
                'count' => collect($todays_jobs)->count(),
                'jobs'  => collect($todays_jobs)
            ],
            'tomorrow' => [
                'count' => collect($tomorrows_jobs)->count(),
                'jobs'  => collect($tomorrows_jobs)
            ]
        ];
    }

    private function makeQuery()
    {
        $scheduled_jobs_query = Job::with('partnerOrder.order', 'jobServices')->whereNotIn('status', ['Served', 'Cancelled']);
        if ($this->user->is_cm) $scheduled_jobs_query->forCM($this->user->id);
        return $scheduled_jobs_query;
    }

    /**
     * @param $jobs
     * @return array
     */
    private function processData($jobs)
    {
        $scheduledJobs = [
            'today'     => [],
            'tomorrow'  => []
        ];

        foreach ($jobs as $job) {
            if($this->today->eq(Carbon::parse($job->schedule_date))) $scheduledJobs['today'][] = $this->formatJobData($job);
            if($this->tomorrow->eq(Carbon::parse($job->schedule_date))) $scheduledJobs['tomorrow'][] = $this->formatJobData($job);;
        }

        return [$scheduledJobs['today'], $scheduledJobs['tomorrow']];
    }

    private function formatJobData($job)
    {
        $order = $job->partnerOrder->order;

        $job = [
            'order_id'      => $order->id,
            'order_code'    => $order->code(),
            'status'        => $job->status,
            'preferred_time'=> ($order->id > config('sheba.last_order_id_for_old_version')) ? $this->formatPreferredTime($job->preferred_time) : $job->preferred_time,
            'service_name'  => $job->service_id ? $job->service_name : $job->jobServices->implode('name', ', '),
            'customer_name' => $order->delivery_name,
        ];
        return $job;
    }

    private function formatPreferredTime($preferred_time)
    {
        $explode_preferred_time = explode('-', $preferred_time);
        return Carbon::parse($explode_preferred_time[0])->format('h:i A') . ' - ' . Carbon::parse($explode_preferred_time[1])->format('h:i A');
    }
}