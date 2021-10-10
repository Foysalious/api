<?php namespace App\Http\Middleware;

use App\Exceptions\NotFoundException;
use App\Models\Customer;
use App\Models\Job;
use Closure;
use Illuminate\Http\Request;

class CustomerJobAuthMiddleware extends AccessTokenMiddleware
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

        $customer = $request->customer;

        if ($request->remember_token != $customer->remember_token) {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this user."]);
        }

        $job = Job::with('partnerOrder.order')->find((int)$request->job);
        if (!$job) {
            return api_response($request, null, 404, ["message" => "Order not found."]);
        }
        if ($job->partnerOrder->order->customer_id != $customer->id) {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this order."]);
        }
        $request->merge(['job' => $job]);
        return $next($request);
    }

    protected function setExtraDataToRequest($request)
    {
        if (!$this->authorizationToken->authorizationRequest->profile) return;

        $customer = Customer::find($this->authUser->getCustomerId());
        if (!$customer) throw new NotFoundException('User not found.', 404);
        if ($customer->id != (int)$request->customer->id) throw new NotFoundException("You're not authorized to access this user.", 403);
        $job = Job::with('partnerOrder.order')->find((int)$request->job);
        if (!$job) throw new NotFoundException("Order not found.", 404);
        if ($job->partnerOrder->order->customer_id != $customer->id) throw new NotFoundException("You're not authorized to access this order.", 403);
        $request->merge(['job' => $job]);
    }
}