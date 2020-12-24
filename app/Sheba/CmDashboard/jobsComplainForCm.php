<?php namespace Sheba\CmDashboard;

use App\Models\Job;
use Illuminate\Support\Facades\Auth;

class jobsComplainForCm
{
    public function __construct()
    {
        $this->cm = Auth::user()->id;
    }

    public function get()
    {
        $data = [];
        Job::with('complains')->forCM($this->cm)->get()->filter(function ($job) use (&$data) {
            if ($job->complains->isEmpty()) return false;
            $job->setRelations([
                'complains' => $job->complains->filter(function ($complain) {
                    return in_array($complain->status, ['Open', 'Observation']);
                })
            ]);
            if (!$job->complains->isEmpty()) array_push($data, $job);
        });

        #return !$job->complains->isEmpty() && in_array_any(['Open', 'Observation'], $job->complains->pluck('status')->toArray());
        return $this->processData(collect($data));
    }

    private function processData($jobs)
    {
        if (!$jobs->isEmpty()) {
            $complains = [];
            $jobs->each(function ($job) use (&$complains) {
                $job->complains->each(function ($complain) use (&$complains, $job) {
                    $complainData = [
                        'complain_id'    => $complain->id,
                        'complain_code'  => $complain->code(),
                        'job_id'         => $job->id,
                        'job_code'       => $job->code(),
                        'complain_status'=> $complain->status
                    ];
                    array_push($complains, $complainData);
                });
            });
            return ['code' => 200, 'complains' => collect($complains)];
        }
        return ['code' => 400];
    }
}