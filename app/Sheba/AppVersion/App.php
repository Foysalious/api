<?php namespace Sheba\AppVersion;


use Sheba\Helpers\Http\ShebaRequestHeader;
use Sheba\Portals\Portals;

class App
{
    /** @var AppVersionManager */
    private $versionManager;

    /** @var string */
    private $platformName;
    /** @var string */
    private $portalName;
    /** @var int */
    private $versionCode;
    /** @var string */
    private $versionName;

    public function __construct(AppVersionManager $version_manager)
    {
        $this->versionManager = $version_manager;
    }

    /**
     * @param string $portal_name
     * @return $this
     */
    public function setPortalName($portal_name)
    {
        $this->portalName = $portal_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPortalName()
    {
        return $this->portalName;
    }

    /**
     * @param string $platform_name
     * @return $this
     */
    public function setPlatformName($platform_name)
    {
        $this->platformName = $platform_name;
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
     * @param int $version_code
     * @return $this
     */
    public function setVersionCode($version_code)
    {
        $this->versionCode = $version_code;
        if (!$this->versionName) $this->setVersionName($this->versionManager->convertIntToSemver($version_code));
        return $this;
    }

    /**
     * @return int
     */
    public function getVersionCode()
    {
        return $this->versionCode;
    }

    /**
     * @param string $version_name
     * @return $this
     */
    public function setVersionName($version_name)
    {
        $this->versionName = $version_name;
        if (!$this->versionCode) $this->setVersionCode($this->versionManager->convertSemverToInt($version_name));
        return $this;
    }

    public function getVersionName()
    {
        return $this->versionName;
    }

    /**
     * @return bool
     */
    public function isIos()
    {
        return $this->getPlatformName() == Apps::IOS_PLATFORM;
    }

    public function getName()
    {
        return AppBuilder::getNameFromPortalAndPlatform($this->getPortalName(), $this->getPlatformName());
    }

    public function getMarketName()
    {
        $name = $this->getName();
        if (!$name) return null;
        return Apps::getMarketNames()[$name];
    }

    public function getPackageName()
    {
        $name = $this->getName();
        if (!$name) return null;
        return Apps::getPackageNames()[$name];
    }

    public function isUsingShebaAccountKit()
    {
        $version = $this->getVersionCode();
        $portal_name = $this->getPortalName();

        return ($version > 30211 && $portal_name == Portals::CUSTOMER_APP) ||
            ($version > 12003 && $portal_name == Portals::BONDHU_APP) ||
            ($version > 2145 && $portal_name == Portals::RESOURCE_APP) ||
            ($version > 126 && $portal_name == Portals::CUSTOMER_APP && $this->isIos());
    }

    public function hasCriticalUpdate()
    {
        $app_name = $this->getName();
        if (!$app_name) return false;

        $version = $this->getVersionCode();
        return $version && $this->versionManager->hasCriticalUpdate($app_name, $version);
    }

    /**
     * @param $version_name
     * @param $data
     * @return mixed
     */
    public function createNewVersion($version_name, $data)
    {
        $result = $this->versionManager->createNewVersion($this, $version_name, $data);
        $this->setVersionName($version_name);
        return $result;
    }
}
