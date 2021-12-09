<?php namespace Sheba\Partner\DataMigration;


use App\Models\Partner;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\PosCustomerService\SmanagerUserServerClient;
use App\Sheba\PosOrderService\PosOrderServerClient;
use App\Sheba\UserMigration\Modules;
use App\Sheba\UserMigration\UserMigrationRepository;
use App\Sheba\UserMigration\UserMigrationService;
use Exception;
use Sheba\Dal\UserMigration\UserStatus;

class PartnerDataMigration
{

    /**
     * @var Partner
     */
    private $partner;
    private $isInventoryMigrated;
    private $isPosOrderMigrated;
    private $isPosCustomerMigrated;

    /**
     * @param Partner $partner
     * @return PartnerDataMigration
     */
    public function setPartner(Partner $partner): PartnerDataMigration
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $isInventoryMigrated
     * @return PartnerDataMigration
     */
    public function setIsInventoryMigrated($isInventoryMigrated): PartnerDataMigration
    {
        $this->isInventoryMigrated = $isInventoryMigrated;
        return $this;
    }

    /**
     * @param mixed $isPosOrderMigrated
     * @return PartnerDataMigration
     */
    public function setIsPosOrderMigrated($isPosOrderMigrated): PartnerDataMigration
    {
        $this->isPosOrderMigrated = $isPosOrderMigrated;
        return $this;
    }

    /**
     * @param mixed $isPosCustomerMigrated
     * @return PartnerDataMigration
     */
    public function setIsPosCustomerMigrated($isPosCustomerMigrated): PartnerDataMigration
    {
        $this->isPosCustomerMigrated = $isPosCustomerMigrated;
        return $this;
    }

    private function generatePartnerMigrationDataForInventory(): array
    {
        return [
            'id' => $this->partner->id,
            'sub_domain' => $this->partner->sub_domain,
            'vat_percentage' => $this->partner->posSetting ? $this->partner->posSetting->vat_percentage : 0.0,
            'created_at' => $this->partner->created_at->subHour(6)->format('Y-m-d H:i:s'),
            'created_by_name' => $this->partner->created_by_name,
            'updated_at' => $this->partner->updated_at->subHour(6)->format('Y-m-d H:i:s'),
            'updated_by_name' => $this->partner->updated_by_name,
        ];
    }

    private function generatePartnerMigrationDataForPosOrder(): array
    {
        $pos_setting = $this->partner->posSetting;
        return [
            'id' => $this->partner->id,
            'name' => $this->partner->name,
            'sub_domain' => $this->partner->sub_domain,
            'qr_code_account_type' => $this->partner->qr_code_account_type,
            'qr_code_image' => $this->partner->qr_code_image,
            'sms_invoice' => $pos_setting ? $this->partner->posSetting->sms_invoice : 0,
            'auto_printing' => $pos_setting ? $this->partner->posSetting->auto_printing : 0,
            'printer_name' => $pos_setting ? $this->partner->posSetting->printer_name : null,
            'printer_model' => $pos_setting ? $this->partner->posSetting->printer_model : null,
            'created_at' => $this->partner->created_at->subHours(6)->format('Y-m-d H:i:s'),
            'created_by_name' => $this->partner->created_by_name,
            'updated_at' => $this->partner->updated_at->subHours(6)->format('Y-m-d H:i:s'),
            'updated_by_name' => $this->partner->updated_by_name,
        ];
    }

    private function generatePartnerMigrationDataToSmanagerUser(): array
    {
        return [
            'originalId' => $this->partner->id,
            'name' => $this->partner->name,
            'sub_domain' => $this->partner->sub_domain,
        ];
    }

    public function migrate()
    {
        try {
            if (!$this->isInventoryMigrated || $this->partner->posSetting) {
                /** @var InventoryServerClient $InventoryClient */
                $InventoryClient = app(InventoryServerClient::class);
                $InventoryClient->post('api/v1/partners/'.$this->partner->id.'/migrate', ['partner_info' => $this->generatePartnerMigrationDataForInventory()]);
            }
            if (!$this->isPosOrderMigrated || $this->partner->posSetting || $this->partner->qr_code_account_type || $this->partner->qr_code_image) {
                /** @var PosOrderServerClient $posOrderClient */
                $posOrderClient = app(PosOrderServerClient::class);
                $posOrderClient->post('api/v1/partners/'.$this->partner->id.'/migrate', ['partner_info' => $this->generatePartnerMigrationDataForPosOrder()]);
            }
            if (!$this->isPosCustomerMigrated) {
                /** @var SmanagerUserServerClient $userClient */
                $userClient = app(SmanagerUserServerClient::class);
                $userClient->post('api/v1/partners/'.$this->partner->id.'/migrate', ['partner_info' => $this->generatePartnerMigrationDataToSmanagerUser()]);
            }
        } catch (Exception $e) {
            $this->storeLogs(0);
            app('sentry')->captureException($e);
        }

    }

    private function storeLogs($isMigrated = 1)
    {
        if ($isMigrated == 0) {
            /** @var UserMigrationService $userMigrationSvc */
            $userMigrationSvc = app(UserMigrationService::class);
            /** @var UserMigrationRepository $class */
            $class = $userMigrationSvc->resolveClass(Modules::POS);
            $class->setUserId($this->partner->id)->setModuleName(Modules::POS)->updateStatus(UserStatus::FAILED);
        }
    }
}