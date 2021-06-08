<?php namespace App\Http\Middleware;

use App\Models\Vendor;
use Closure;
use Illuminate\Http\Request;

class VendorMiddleware
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
        if ($request->hasHeader('app-key') && $request->hasHeader('app-secret')) {
            $vendor = Vendor::where([
                ['app_key', $request->header('app-key')],
                ['app_secret', $request->header('app-secret')],
                ['whitelisted_ip', getIp()],
                ['is_active', 1]
            ])->first();
            $request->merge(['vendor' => $vendor]);
            return $vendor ? $next($request) : response()->json(['code' => 403, 'message' => 'Unauthorized request']);
        } else {
            return response()->json(['code' => 400, 'message' => 'Authorization headers missing']);
        }
    }
}
