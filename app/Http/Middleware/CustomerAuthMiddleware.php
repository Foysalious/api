<?php namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use JWTAuth;
use App\Exceptions\NotFoundException;

class CustomerAuthMiddleware extends AccessTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->getToken();

        if ($token) return parent::handle($request, $next);

        if (!$request->has('remember_token')) {
            return api_response($request, null, 400, ["message" => "Authentication token is missing from the request."]);
        }

        $customer = Customer::where('remember_token', $request->input('remember_token'))->first();
        if (!$customer) {
            return api_response($request, null, 404, ["message" => "User not found."]);
        }

        if ($customer->id != $request->customer) {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this user."]);
        }

        $request->merge(['customer' => $customer]);
        return $next($request);
    }

    protected function setExtraDataToRequest($request)
    {
        if (!$this->authorizationToken->authorizationRequest->profile) return;

        $customer = Customer::find($this->authUser->getCustomerId());
        if (!$customer) throw new NotFoundException('User not found.', 404);
        if ($customer->id != (int)$request->customer) throw new NotFoundException("You're not authorized to access this user.", 403);
        $request->merge(['customer' => $customer]);
    }
}
