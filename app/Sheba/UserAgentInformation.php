<?php namespace Sheba;


use Illuminate\Http\Request;
use Sheba\AppVersion\Apps;
use Sheba\Portals\Portals;

class UserAgentInformation
{
    /** @var Request */
    private $request;
    private $ip;
    private $versionCode;
    private $portalName;
    private $platformName;
    private $userAgent;

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
        $this->setIp($this->request->ip());
        $this->setVersionCode($sheba_request_header->getVersionCode());
        $this->setPlatformName($sheba_request_header->getPlatformName());
        $this->setUserAgent($this->request->header('User-Agent'));
        $this->request = null;
    }

    private function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    private function setVersionCode($versionCode)
    {
        $this->versionCode = $versionCode;
        return $this;
    }

    private function setPortalName($portalName)
    {
        $this->portalName = $portalName;
        return $this;
    }

    private function setPlatformName($platform)
    {
        $this->platformName = $platform;
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

    public function getVersionCode()
    {
        return $this->versionCode;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function getPlatformName()
    {
        return $this->platformName;
    }

    public function getApp()
    {
        if ($this->portalName == Portals::CUSTOMER_APP) {
            if ($this->platformName == 'ios') return Apps::CUSTOMER_APP_IOS;
            return Apps::CUSTOMER_APP_ANDROID;
        }

        if ($this->portalName == Portals::EMPLOYEE_APP) {
            if ($this->platformName == 'ios') return Apps::EMPLOYEE_APP_IOS;
            return Apps::EMPLOYEE_APP_ANDROID;
        }

        if ($this->portalName == Portals::RESOURCE_APP) {
            if ($this->platformName == 'ios') return Apps::RESOURCE_APP_IOS;
            return Apps::RESOURCE_APP_ANDROID;
        }

        if ($this->portalName == Portals::BONDHU_APP) return Apps::BONDHU_APP_ANDROID;
        if ($this->portalName == Portals::PARTNER_APP) return Apps::MANAGER_APP_ANDROID;

        return null;
    }
}