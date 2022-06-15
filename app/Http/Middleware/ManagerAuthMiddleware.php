<?php namespace App\Http\Middleware;

use App\Models\Partner;
use App\Models\Resource;
use Closure;
use Illuminate\Http\Request;
use App\Exceptions\NotFoundException;
use Sheba\ModificationFields;

class ManagerAuthMiddleware extends AccessTokenMiddleware
{
    use ModificationFields;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $this->getToken();

        if ($token) return parent::handle($request, $next);

        return $this->handleRememberToken($request, $next);
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    private function handleRememberToken(Request $request, Closure $next)
    {
        if (!$request->filled('remember_token')) {
            return api_response($request, null, 400, ["message" => "Authentication token is missing from the request."]);
        }

        /** @var Resource $manager_resource */
        $manager_resource = Resource::where('remember_token', $request->input('remember_token'))->first();
        if (!$manager_resource) {
            return api_response($request, null, 404, ["message" => 'Resource not found.']);
        }

        /** @var Partner $partner */
        $partner = Partner::find($request->partner);
        if (!$partner) {
            return api_response($request, null, 404, ["message" => 'Partner not found.']);
        }

        if (!$manager_resource->isManager($partner)) {
            return api_response($request, null, 403, ["message" => "Forbidden. You're not a manager of this partner."]);
        }

        $request->merge(['manager_resource' => $manager_resource, 'partner' => $partner]);

        $this->setModifier($manager_resource);

        return $next($request);
    }

    protected function setExtraDataToRequest($request)
    {
        if (!$this->authorizationToken->authorizationRequest->profile) return;

        /** @var Resource $manager_resource */
        $manager_resource = $this->authUser->getResource();
        if (!$manager_resource) throw new NotFoundException('Resource not found.', 404);

        /** @var Partner $partner */
        $partner = Partner::find($request->partner);
        if (!$partner) throw new NotFoundException('Partner not found.', 404);

        if (!$manager_resource->isManager($partner)) throw new NotFoundException("Forbidden. You're not a manager of this partner.", 403);

        $this->setModifier($manager_resource);

        $request->merge(['manager_resource' => $manager_resource, 'partner' => $partner]);
    }
}
