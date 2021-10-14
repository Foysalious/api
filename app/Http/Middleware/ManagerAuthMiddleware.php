<?php namespace App\Http\Middleware;

use App\Models\Partner;
use App\Models\Resource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ManagerAuthMiddleware
{
    use UserMigrationCheckMiddleware;
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('remember_token')) {
            /** @var Resource $manager_resource */
            $manager_resource = Resource::where('remember_token', $request->input('remember_token'))->first();
            $partner = Partner::find($request->partner);
            if ($manager_resource && $partner) {
//                if (!$this->isRouteAccessAllowed($partner)) {
//                    return api_response($request, null, 403, ["message" => "Sorry! Your migration is running. Please be patient."]);
//                }
                if ($manager_resource->isManager($partner)) {
                    $request->merge(['manager_resource' => $manager_resource, 'partner' => $partner]);
                    return $next($request);
                } else {
                    return api_response($request, null, 403, ["message" => "Forbidden. You're not a manager of this partner."]);
                }
            } else {
                return api_response($request, null, 404, ["message" => 'Partner or Resource not found.']);
            }
        } else {
            return api_response($request, null, 400, ["message" => "Authentication token is missing from the request."]);
        }
    }
}
