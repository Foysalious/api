<?php namespace Sheba\CmDashboard;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EstimatedJobs
{
    private $cm;
    private $type;

    public function __construct($type)
    {
        $this->cm   = Auth::user()->id;
        $this->type = $type;
    }

    public function get()
    {
        $type       = $this->type;
        $today      = Carbon::today();
        $tomorrow   = Carbon::tomorrow();

        $estimatedJobs = [
            'today' => [],
            'tomorrow' => []
        ];

        $jobs = Job::forCM($this->cm)->whereNotNull($type)->whereNotIn('status', ['Served', 'Cancelled'])->get();

        foreach ($jobs as $job) {
            if($today->eq(Carbon::parse($job->$type->toDateString() . ' 00:00:00'))) $estimatedJobs['today'][] = $job;
            if($tomorrow->eq(Carbon::parse($job->$type->toDateString() . ' 00:00:00'))) $estimatedJobs['tomorrow'][] = $job;
        }

        return $estimatedJobs = [
            'today'     => [
                'count' => collect($estimatedJobs['today'])->count(),
                'jobs'  => collect($estimatedJobs['today'])
            ],
            'tomorrow'  => [
                'count' => collect($estimatedJobs['tomorrow'])->count(),
                'jobs'  => collect($estimatedJobs['tomorrow'])
            ]
        ];
    }
}