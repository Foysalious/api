<?php namespace App\Http\Middleware;


use Illuminate\Http\Request;
use Sheba\Dal\ApiRequest\ApiRequestRepositoryInterface;

use Closure;

class ApiRequestMiddleware
{
    private $apiRequestRepository;

    public function __construct(ApiRequestRepositoryInterface $apiRequestRepository)
    {
        $this->apiRequestRepository = $apiRequestRepository;
    }

    public function handle(Request $request, Closure $next)
    {
        $api_request = $this->apiRequestRepository->create([
            'route' => $request->fullUrl(),
            'ip' => getIp(),
            'user_agent' => $request->header('User-Agent'),
            'portal' => $request->header('portal-name'),
            'portal_version' => $request->header('Version-Code'),
            'google_advertising_id' => $request->google_advertising_id,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'uuid' => $request->uuid,
            'firebase_token' => $request->firebase_token
        ]);
        $request->merge(['api_request' => $api_request]);
        return $next($request);
    }
}