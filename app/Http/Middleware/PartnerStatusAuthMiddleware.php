<?php


namespace App\Http\Middleware;


use App\Exceptions\DoNotReportException;
use App\Models\Partner;
use Closure;
use Sheba\Authentication\Exceptions\AuthenticationFailedException;
use Sheba\Partner\PartnerStatuses;

class PartnerStatusAuthMiddleware
{
    /**
     * @var array[]
     */
    protected $access;

    public function __construct()
    {
        $this->access = [
            'both'        => [PartnerStatuses::BLACKLISTED, PartnerStatuses::PAUSED],
            'blacklisted' => [PartnerStatuses::BLACKLISTED],
            'paused'      => [PartnerStatuses::PAUSED]
        ];
    }

    /**
     * @param         $request
     * @param Closure $next
     * @param string  $role
     * @return mixed
     * @throws AuthenticationFailedException
     * @throws DoNotReportException
     */
    public function handle($request, Closure $next, $role = "both")
    {
        if (!isset($request->partner) && $request->partner instanceof Partner) {
            throw new DoNotReportException("Not a Partner");
        }
        if (in_array($request->partner->status,$this->access[$role])){
            throw new AuthenticationFailedException("You are not allowed to access this url");
        }
        return $next($request);
    }
}
