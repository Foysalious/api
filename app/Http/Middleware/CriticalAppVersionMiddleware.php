<?php namespace App\Http\Middleware;

use Closure;
use Sheba\AppVersion\AppVersionManager;
use Sheba\UserAgentInformation;

class CriticalAppVersionMiddleware
{
    protected $except = [
        'v1/versions'
    ];

    /** @var UserAgentInformation  */
    private $userAgentInfo;
    /** @var AppVersionManager */
    private $appVersionManager;

    public function __construct(UserAgentInformation $user_agent_info, AppVersionManager $app_version_manager)
    {
        $this->userAgentInfo = $user_agent_info;
        $this->appVersionManager = $app_version_manager;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (in_array($request->path(), $this->except)) return $next($request);

        $this->userAgentInfo->setRequest($request);
        $app = $this->userAgentInfo->getApp();
        $version = $this->userAgentInfo->getVersionCode();

        if ($app && $version && $this->appVersionManager->hasCriticalUpdate($app, $version)) {
            return api_response($request, null, 410);
        }

        return $next($request);
    }
}
