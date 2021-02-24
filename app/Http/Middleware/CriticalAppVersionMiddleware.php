<?php namespace App\Http\Middleware;

use Closure;
use Sheba\AppVersion\AppHasBeenDeprecated;
use Sheba\AppVersion\AppVersionManager;
use Sheba\UserAgentInformation;

class CriticalAppVersionMiddleware
{
    protected $except = [
        'v1/versions'
    ];

    /** @var UserAgentInformation  */
    private $userAgentInfo;

    public function __construct(UserAgentInformation $user_agent_info)
    {
        $this->userAgentInfo = $user_agent_info;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws AppHasBeenDeprecated
     */
    public function handle($request, Closure $next)
    {
        if (in_array($request->path(), $this->except)) return $next($request);

        $this->userAgentInfo->setRequest($request);

        $app = $this->userAgentInfo->getApp();
        if (!$app) return $next($request);

        if ($app->hasCriticalUpdate()) throw new AppHasBeenDeprecated($app);

        return $next($request);
    }
}
