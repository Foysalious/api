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
        return $this->getPlatformName() == 'ios';
    }

    public function getName()
    {
        $portal_name = $this->getPortalName();
        $is_ios = $this->isIos();

        if ($portal_name == Portals::CUSTOMER_APP) {
            if ($is_ios) return Apps::CUSTOMER_APP_IOS;
            return Apps::CUSTOMER_APP_ANDROID;
        }

        if ($portal_name == Portals::EMPLOYEE_APP) {
            if ($is_ios) return Apps::EMPLOYEE_APP_IOS;
            return Apps::EMPLOYEE_APP_ANDROID;
        }

        if ($portal_name == Portals::RESOURCE_APP) {
            if ($is_ios) return Apps::RESOURCE_APP_IOS;
            return Apps::RESOURCE_APP_ANDROID;
        }

        if ($portal_name == Portals::BONDHU_APP) return Apps::BONDHU_APP_ANDROID;
        if ($portal_name == Portals::PARTNER_APP) return Apps::MANAGER_APP_ANDROID;

        return null;
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
     * @param ShebaRequestHeader $header
     * @return App|null
     */
    public static function build(ShebaRequestHeader $header)
    {
        if (Portals::isNotApp($header->getPortalName())) return null;

        /** @var App $app */
        $app = app(App::class);

        return $app->setPortalName($header->getPortalName())
            ->setPlatformName($header->getPlatformName())
            ->setVersionName($header->getVersionCode());
    }
}
