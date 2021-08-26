<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Redis;

class ConcurrentRequestMiddleware
{
    private $paramNames = ['job'];
    private $actions = ['collect', 'pay'];

    public function handle($request, Closure $next, $user='resource', $paramName = 'job', $action = 'collect')
    {
        if (!in_array($paramName, $this->paramNames) || !in_array($action, $this->actions)) return $next($request);

        $duration = constants('MAX_CONCURRENT_MIDDLEWARE_TIME');
        $key = 'job_' . $this->getJobId($request, $paramName);

        if ($data = Redis::get($key)){
            $data = json_decode($data, true);
            return response()->json(['code' => 429, 'message' =>  $this->generateMsg($data) . ' You need to wait at least ' . $duration/60 . ' minutes before requesting again.']);
        }

        $value = ['created_at' => Carbon::now(), 'action' => $action, 'created_by' => $user];
        Redis::set($key, json_encode($value));
        Redis::expire($key, $duration);
        return $next($request);
    }

    private function getJobId($request, $paramName)
    {
        if ($paramName === 'job') {
            return $request->route($paramName)->id;
        }
    }

    private function generateMsg($data)
    {
        if ($data['action'] === 'pay' || $data['action'] === 'collect'){
            return $data['created_by'] . ' requested to ' . $data['action'] . ' for this order a few seconds ago.';
        }
        return $data['created_by'] . ' requested to update the order a few seconds ago.';
    }
}
