<?php namespace Sheba\CmDashboard;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CsatDueJobs
{
    private $user;
    private $today;
    private $yesterday;

    public function __construct()
    {
        $this->user      = Auth::user();
        $this->today     = Carbon::today();
        $this->yesterday = Carbon::yesterday();
    }

    public function get()
    {
        $jobs = $this->makeQuery()->get();
        list($today_jobs, $yesterday_jobs) = $this->processData($jobs);

        return $scheduledJobs = [
            'yesterday' => [
                'count' => collect($yesterday_jobs)->count(),
                'jobs'  => collect($yesterday_jobs)
            ],
            'today' => [
                'count' => collect($today_jobs)->count(),
                'jobs'  => collect($today_jobs)
            ],
            'all' => [
                'count' => collect($jobs)->count(),
                'jobs'  => collect($jobs)
            ]
        ];
    }

    private function makeQuery()
    {
        return Job::with('partnerOrder.order')
                ->where('status', 'Served')
                ->whereNotIn('id', function ($q) {
                    return $q->select('job_id')->from('reviews');
                })->forCM($this->user->id);
    }

    /**
     * @param $jobs
     * @return array
     */
    private function processData($jobs)
    {
        $csat_due_jobs = [
            'today'     => [],
            'yesterday'  => []
        ];

        foreach ($jobs as $job) {
            $delivered_date = Carbon::parse($job->delivered_date);
            if($delivered_date->isToday()) $csat_due_jobs['today'][] = $job;
            elseif($delivered_date->isYesterday()) $csat_due_jobs['yesterday'][] = $job;
        }

        return [$csat_due_jobs['today'], $csat_due_jobs['yesterday']];
    }
}