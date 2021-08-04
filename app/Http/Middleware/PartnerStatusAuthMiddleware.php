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
     * @return mixed
     * @throws AuthenticationFailedException
     * @throws DoNotReportException
     */
    public function handle($request, Closure $next)
    {
        if (!isset($request->partner) && $request->partner instanceof Partner) {
            throw new DoNotReportException("Not a Partner");
        }
        $this->generateException($request->partner->status);

        return $next($request);
    }

    /**
     * @throws AuthenticationFailedException
     */
    protected function generateException($status)
    {
        if($status === $this->access['blacklisted'][0])
            throw new AuthenticationFailedException("You are not allowed to access this url");

        elseif ($status === $this->access['paused'][0])
            throw new AuthenticationFailedException("You are temporarily suspended to access this url", 403);

    }
}
