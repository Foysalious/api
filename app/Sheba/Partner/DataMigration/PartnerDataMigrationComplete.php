<?php namespace Sheba\Partner\DataMigration;


use App\Exceptions\DataMismatchException;
use App\Models\PartnerPosCustomer;
use App\Models\PartnerPosService;
use App\Models\PosOrder;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\PosCustomerService\SmanagerUserServerClient;
use App\Sheba\PosOrderService\PosOrderServerClient;
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
        if (count($keys) > 0) return;
        if($this->isDataCountMatched()) {
            $this->updateStatusTo(UserStatus::UPGRADED);
        } else {
            $this->updateStatusTo(UserStatus::FAILED);
        }
    }


    /**
     * @throws Exception
     */
    private function updateStatusTo($status)
    {
        /** @var UserMigrationService $userMigrationSvc */
        $userMigrationSvc = app(UserMigrationService::class);
        /** @var UserMigrationRepository $class */
        $class = $userMigrationSvc->resolveClass(Modules::POS);
        $current_status = $class->setUserId($this->partnerId)->setModuleName(Modules::POS)->getStatus();
        if ($current_status == UserStatus::UPGRADING) $class->updateStatus($status);
    }

    /**
     * @throws Exception
     */
    private function isDataCountMatched(): bool
    {
        try {
            $partner_pos_service = PartnerPosService::where('partner_id', $this->partnerId)->withTrashed()->count();
            $inventoryClient = app(InventoryServerClient::class);
            $inventoryResponse = $inventoryClient->get('api/v1/partners/' . $this->partnerId . '/partner-product-count');
            if ($inventoryResponse['count'] < $partner_pos_service) {
                app('sentry')->captureException(new DataMismatchException('Partner #' . $this->partnerId .
                    ' inventory data mismatch!. Previous: '. $partner_pos_service. '. After Migration: '. $inventoryResponse['count']));
                return false;
            }
            $pos_orders = PosOrder::where('partner_id', $this->partnerId)->withTrashed()->count();
            $posOrderClient = app(PosOrderServerClient::class);
            $posOrderResponse = $posOrderClient->get('api/v1/partners/' . $this->partnerId . '/partner-order-count');
            if ($posOrderResponse['count'] < $pos_orders) {
                app('sentry')->captureException(new DataMismatchException('Partner #' . $this->partnerId .
                    ' orders data mismatch!. Previous: '. $pos_orders. '. After Migration: '. $posOrderResponse['count']));
                return false;
            }
            $partner_pos_customers = PartnerPosCustomer::where('partner_id', $this->partnerId)->count();
            $smanagerUserClient = app(SmanagerUserServerClient::class);
            $smanagerUserResponse = $smanagerUserClient->get('api/v1/partners/' . $this->partnerId . '/user-counts');
            if ($smanagerUserResponse['count'] < $partner_pos_customers) {
                app('sentry')->captureException(new DataMismatchException('Partner #' . $this->partnerId .
                    ' customers data mismatch!. Previous: '. $partner_pos_customers. '. After Migration: '. $smanagerUserResponse['count']));
                return false;
            }
        } catch (Exception $e) {
            app('sentry')->captureException($e);
            $this->updateStatusTo(UserStatus::FAILED);
        }
        return true;
    }
}