<?php

namespace App\Http\Middleware;

use App\Sheba\Jobs\JobConcurrencyHandler;
use Closure;

class ConcurrentOrderServeCollectMiddleware
{
    private $jobConcurrencyHandler;
    public function __construct(JobConcurrencyHandler $jobConcurrencyHandler)
    {
        $this->jobConcurrencyHandler = $jobConcurrencyHandler;
    }

    public function handle($request, Closure $next, $action)
    {
        $jobId = $this->getJobId($request);
        $redisData = $this->jobConcurrencyHandler->getJobIfExistsInRedis($jobId);

        if ($redisData){
            $redisData = json_decode($redisData);
            return response()->json(['code' => 429, 'message' =>  $this->generateMsg($redisData)]);
        }

        $this->jobConcurrencyHandler->storeJobToRedis($jobId, $action);
        return $next($request);
    }

    private function getJobId($request)
    {
        if (gettype($request->route('job')) == 'object') return $request->route('job')->id;
        return (int)$request->route('job');
    }

    private function generateMsg($redisData)
    {
        $msg = '';
        if ($redisData->action === 'collection') $msg = 'Money collection for this job is under process. ';
        elseif ($redisData->action === 'status') $msg = 'Status update for this job is under process. ';
        return $msg . 'Please try again after a few seconds.';
    }
}
