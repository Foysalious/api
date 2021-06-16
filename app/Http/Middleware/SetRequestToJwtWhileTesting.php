<?php namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class SetRequestToJwtWhileTesting
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->runningUnitTests()) {
            JWTAuth::setRequest($request);
        }

        return $next($request);
    }
    private function runningUnitTests()
    {
        $app = app();
        return $app->runningInConsole() && $app->runningUnitTests();
    }

}