<?php namespace App\Http\Middleware\TopUp;

use App\Models\Partner;
use Closure;

class TopUpAuthMiddleware
{

    public function __construct()
    {
        dd(1212);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        dd(1);
    }
}
