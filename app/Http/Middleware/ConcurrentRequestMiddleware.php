<?php

namespace App\Http\Middleware;

use App\Models\Job;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\JobService\JobService;

class ConcurrentRequestMiddleware
{
    private $paramNames = ['job', 'order', 'job_service'];
    private $actions = ['collect', 'pay', 'update'];

    public function handle($request, Closure $next, $user='resource', $action = 'collect', $paramName = 'job')
    {
        if (!in_array($paramName, $this->paramNames) || !in_array($action, $this->actions)) return response()->json(['code' => 500, 'message' => 'Invalid parameter name']);

        $duration = constants('MAX_CONCURRENT_MIDDLEWARE_TIME');
        try {
            $key = 'job_' . $this->getJobId($request, $paramName);
        } catch (\Exception $e) {
            return response()->json(['code' => 400, 'message' => 'Invalid parameters']);
        }

        if (($data = Redis::get($key)) && ($action === 'collect' || $action === 'pay')){
            $data = json_decode($data, true);
            return response()->json(['code' => 429, 'message' =>  $this->generateMsg($data, $user) . ' You need to wait at least ' . $this->getTimeLeft($duration, $data) . ' before requesting again.']);
        }

        $value = ['created_at' => time(), 'action' => $action, 'created_by' => $user];
        Redis::set($key, json_encode($value));
        Redis::expire($key, $duration);
        return $next($request);
    }

    private function getJobId($request, $paramName)
    {
        if ($paramName === 'job') {
            if(gettype($request->route($paramName)) == 'object') return $request->route($paramName)->id;
            return (int)$request->route($paramName);
        }

        if ($paramName === 'order') {
//            The expected order value is partner_order id
            if(gettype($request->route($paramName)) == 'object') return $request->route($paramName)->getActiveJob()->id;
            if(isset($request->partner_order) && !empty($request->partner_order)) return $request->partner_order->getActiveJob()->id;
            return PartnerOrder::find((int)$request->route($paramName))->getActiveJob()->id;
        }

        if ($paramName === 'job_service') {
            if(gettype($request->route($paramName)) == 'object') return $request->route($paramName)->job_id;
            return JobService::find((int)$request->route($paramName))->job_id;
        }
    }

    private function generateMsg($data, $user)
    {
        if ($data['created_by'] === 'admin') $createdBy = 'An admin';
        elseif ($data['created_by'] === $user) $createdBy = 'You';
        else $createdBy = $data['created_by'];

        if ($data['action'] === 'pay' || $data['action'] === 'collect' || $data['action'] === 'refund'){
            return $createdBy . ' requested to ' . $data['action'] . ' for this order a few seconds ago.';
        }
        return $createdBy . ' requested to update the order a few seconds ago.';
    }

    private function getTimeLeft($duration, $data)
    {
        $now = time();
        $timeLeft = $duration - ($now - $data['created_at']);
        $secondsLeft = $timeLeft % 60;
        $minutesLeft = (int)($timeLeft / 60);
        return ($minutesLeft > 0 ? $minutesLeft . ' minute' . ($minutesLeft > 1 ? 's ' : ' ') : '') . "$secondsLeft seconds";
    }
}
