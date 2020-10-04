<?php

namespace App\Http\Middleware;

use Closure;
use Sheba\Dal\PaymentClientAuthentication\Model as PaymentClientAuthentication;

class ExternalPaymentLinkAuthMiddleware
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
        if ($request->has(['client_id', 'secret'])) {
            $client = PaymentClientAuthentication::where('client_id', $request->client_id)->where('client_secret', $request->secret)->first();
            if($client){
                $ip = $request->ip();
                if(in_array($ip, explode(',', $client->whitelisted_ips))) {
                    $request->merge(['client' => $client]);
                    return $next($request);
                }
                else {
                    return api_response($request, null, 400, ["message" => "The ip you are accessing from is not whitelisted."]);
                }
            }
            else {
                return api_response($request, null, 404, ["message" => "Client not found."]);
            }
        }
        else {
            return api_response($request, null, 400, ["message" => "Client id or secret is missing from the request."]);
        }
    }
}
