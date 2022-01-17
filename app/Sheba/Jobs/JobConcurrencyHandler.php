<?php

namespace App\Sheba\Jobs;

use Illuminate\Support\Facades\Redis;

class JobConcurrencyHandler
{
    private $keyPrefix = 'serve_collect_job_';

    public function getJobIfExistsInRedis($jobId)
    {
        return Redis::get($this->keyPrefix . $jobId);
    }

    public function storeJobToRedis($jobId, $action)
    {
        $key = $this->keyPrefix . $jobId;
        $duration = constants('MAX_CONCURRENT_MIDDLEWARE_TIME');
        $value = ['created_at' => time(), 'action' => $action];
        Redis::set($key, json_encode($value));
        Redis::expire($key, $duration);
    }

    public function removeJobFromRedis($jobId)
    {
        $key = $this->keyPrefix . $jobId;
        return Redis::del($key);
    }
}