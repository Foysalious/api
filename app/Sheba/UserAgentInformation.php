<?php namespace Sheba;


use Illuminate\Http\Request;

class UserAgentInformation
{
    /** @var Request */
    private $request;

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getPortalName()
    {
        return $this->request->header('portal-name') != null ? $this->request->header('portal-name') : 'customer-portal';
    }

    public function getIp()
    {
        return $this->request->ip();
    }

    public function getVersionCode()
    {
        return $this->request->header('Version-Code');
    }

    public function getUserAgent()
    {
        return $this->request->header('User-Agent');
    }
}