<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class CheckUserMigrationRunningMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isMigrationRunning = Redis::get("user-migration:$request->partner_id");
        if ($isMigrationRunning) {
            return api_response($request, null, 403, ["message" => "Sorry! Your migration is running for $isMigrationRunning. Please be patient."]);
        }
        return $next($request);
    }
}
