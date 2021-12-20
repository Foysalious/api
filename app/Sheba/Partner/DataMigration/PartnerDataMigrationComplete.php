<?php namespace Sheba\Partner\DataMigration;


use App\Sheba\UserMigration\Modules;
use App\Sheba\UserMigration\UserMigrationRepository;
use App\Sheba\UserMigration\UserMigrationService;
use Exception;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\UserMigration\UserStatus;

class PartnerDataMigrationComplete
{
    private $partnerId;

    /**
     * @param mixed $partnerId
     * @return PartnerDataMigrationComplete
     */
    public function setPartnerId($partnerId): PartnerDataMigrationComplete
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function checkAndUpgrade()
    {
        $keys = Redis::keys('DataMigration::Partner::' . $this->partnerId. '::*');
        if (count($keys) <= 0) $this->updateStatusToUpgraded();
    }


    /**
     * @throws Exception
     */
    private function updateStatusToUpgraded()
    {
        /** @var UserMigrationService $userMigrationSvc */
        $userMigrationSvc = app(UserMigrationService::class);
        /** @var UserMigrationRepository $class */
        $class = $userMigrationSvc->resolveClass(Modules::POS);
        $current_status = $class->setUserId($this->partnerId)->setModuleName(Modules::POS)->getStatus();
        if ($current_status == UserStatus::UPGRADING) $class->updateStatus(UserStatus::UPGRADED);
    }
}