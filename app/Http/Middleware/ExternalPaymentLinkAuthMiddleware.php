<?php

namespace App\Http\Middleware;

use Closure;
use Sheba\Dal\PaymentClientAuthentication\Model as PaymentClientAuthentication;

class ExternalPaymentLinkAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $client_id     = $request->header('client-id');
        $client_secret = $request->header('client-secret');
        if ($request->hasHeader('client-id') && $request->hasHeader('client-secret')) {
            $client = PaymentClientAuthentication::where('client_id', $client_id)->where('client_secret', $client_secret)->first();
            if ($client) {
                if ($client->status != 'published') return api_response($request, null, 501, ['message' => 'This client is not published']);
                $ip = getIp();
                if (in_array($ip, explode(',', $client->whitelisted_ips))) {
                    $request->merge(['client' => $client]);
                    return $next($request);
                }
                return api_response($request, null, 502, ["message" => "The ip `$ip` you are accessing from is not whitelisted."]);
            }
            return api_response($request, null, 504, ["message" => "Client ID and Secret does not match"]);
        }
        return api_response($request, null, 503, ["message" => "Client id or secret is missing from the request."]);
    }
}
