<?php namespace Sheba;


use Illuminate\Http\Request;
use Sheba\AppVersion\App;
use Sheba\AppVersion\AppBuilder;
use Sheba\AppVersion\Apps;
use Sheba\Portals\Portals;

class UserAgentInformation
{
    /** @var Request */
    private $request;
    private $ip;
    private $portalName;
    private $userAgent;
    /** @var App */
    private $app;

    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->resolve();
        return $this;
    }

    /**
     *  request set to null because it can't be serialized in Laravel
     */
    private function resolve()
    {
        $sheba_request_header = getShebaRequestHeader($this->request);

        $this->setPortalName($sheba_request_header->getPortalName() ?: Portals::CUSTOMER_WEB);
        $this->app = AppBuilder::buildFromHeader($sheba_request_header);
        $this->setIp($this->request->ip());
        $this->setUserAgent($this->request->header('User-Agent'));
        $this->request = null;
    }

    private function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    private function setPortalName($portalName)
    {
        $this->portalName = $portalName;
        return $this;
    }

    private function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getPortalName()
    {
        return $this->portalName;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return App | null
     */
    public function getApp()
    {
        return $this->app;
    }

    public function getInformationArray()
    {
        return array(
            'portal_name' => $this->request->header('portal-name') != null ? $this->request->header('portal-name') : 'customer-portal',
            'user_agent' => $this->request->header('User-Agent'),
            'ip' => getIp()
        );
    }
}
