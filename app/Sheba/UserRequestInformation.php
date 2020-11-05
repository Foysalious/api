<?php namespace Sheba;


use Illuminate\Http\Request;

class UserRequestInformation
{
    /** @var Request */
    private $request;
    private $ip;
    private $versionCode;
    private $portalName;
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
        $this->setPortalName($this->request->header('portal-name') != null ? $this->request->header('portal-name') : 'customer-portal');
        $this->setIp($this->request->ip());
        $this->setVersionCode($this->request->header('Version-Code'));
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
}