<?php namespace Sheba\Helpers\Http;


class ShebaRequestHeader
{
    const PORTAL_NAME_KEY = "portal-name";
    const VERSION_CODE_KEY = "Version-Code";
    const PLATFORM_NAME_KEY = "Platform-Name";

    /** @var string */
    private $portalName;
    /** @var string */
    private $versionCode;
    /** @var string */
    private $platformName;

    /**
     * @return mixed
     */
    public function getPortalName()
    {
        return $this->portalName;
    }

    /**
     * @param string $portalName
     * @return ShebaRequestHeader
     */
    public function setPortalName($portalName)
    {
        $this->portalName = $portalName;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersionCode()
    {
        return $this->versionCode;
    }

    /**
     * @param string $versionCode
     * @return ShebaRequestHeader
     */
    public function setVersionCode($versionCode)
    {
        $this->versionCode = $versionCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformName()
    {
        return $this->platformName;
    }

    /**
     * @param string $platformName
     * @return ShebaRequestHeader
     */
    public function setPlatformName($platformName)
    {
        $this->platformName = $platformName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return is_null($this->portalName) && is_null($this->versionCode) && is_null($this->platformName);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $headers = [];
        if (!is_null($this->portalName)) $headers[self::PORTAL_NAME_KEY] = $this->portalName;
        if (!is_null($this->versionCode)) $headers[self::VERSION_CODE_KEY] = $this->versionCode;
        if (!is_null($this->platformName)) $headers[self::PLATFORM_NAME_KEY] = $this->platformName;
        return $headers;
    }
}
