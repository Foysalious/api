<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class IpWhitelistMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next)
    {

        if ($this->runningUnitTests()) return $next($request);
        $key = config('sheba.whitelisted_ip_redis_key_name');
        dd(json_decode(Redis::get($key)));
        if ((config('app.env') == 'local') || ($redis->exists(config('sheba.whitelisted_ip_redis_key_name')) &&
                in_array(getIp(), json_decode($redis->get(config('sheba.whitelisted_ip_redis_key_name')))))) {
            return $next($request);
        }
        throw new AuthorizationException;
    }

    private function runningUnitTests()
    {
        $app = app();
        return $app->runningInConsole() && $app->runningUnitTests();
    }
}