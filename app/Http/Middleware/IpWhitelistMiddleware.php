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
        if ((config('app.env') == 'local') || $key &&
                in_array(getIp(), json_decode($key)))
        {
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