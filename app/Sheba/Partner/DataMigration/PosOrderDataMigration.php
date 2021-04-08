<?php namespace App\Sheba\Partner\DataMigration;


use App\Models\Partner;
use App\Models\PosOrder;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToPosOrderJob;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Illuminate\Support\Collection;
use Sheba\Dal\PosCategory\PosCategoryRepository;
use Sheba\Partner\DataMigration\InventoryDataMigration;
use Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToInventoryJob;
use Sheba\Pos\Repositories\Interfaces\PosDiscountRepositoryInterface;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Pos\Repositories\PosServiceDiscountRepository;
use Sheba\Pos\Repositories\PosServiceLogRepository;
use Sheba\Repositories\PartnerRepository;

class PosOrderDataMigration
{
    const CHUNK_SIZE = 10;
    /** @var Partner */
    private $partner;
    /**
     * @var PosOrderServerClient
     */
    private $client;
    /**
     * @var PartnerRepository
     */
    private $partnerRepository;
    /**
     * @var PosDiscountRepositoryInterface
     */
    private $posOrderDiscountRepository;
    /**
     * @var PosOrderRepository
     */
    private $posOrderRepository;

    private $partnerPosOrders;
    /**
     * @var array
     */
    private $partnerPosOrderIds;


    public function __construct(PartnerRepository $partnerRepository, PosOrderRepository $posOrderRepository, PosDiscountRepositoryInterface $posOrderDiscountRepository, PosOrderServerClient $client)
    {
        $this->partnerRepository = $partnerRepository;
        $this->posOrderRepository = $posOrderRepository;
        $this->posOrderDiscountRepository = $posOrderDiscountRepository;
        $this->client = $client;
    }

    /**
     * @param mixed $partner
     * @return PosOrderDataMigration
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function migrate()
    {
        $this->migratePartner();
        $this->migratePosOrderDiscounts();
    }

    private function migratePartner()
    {
        dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, ['partner_info' => $this->generatePartnerMigrationData()]));
    }


    private function generatePartnerMigrationData()
    {
        $pos_setting = $this->partner->posSetting;
        return [
            'id' => $this->partner->id,
            'sub_domain' => $this->partner->sub_domain,
            'sms_invoice' => $pos_setting ? $this->partner->posSetting->sms_invoice : 0,
            'auto_printing' => $pos_setting ? $this->partner->posSetting->auto_pronting : 0,
            'printer_name' => $pos_setting ? $this->partner->posSetting->printer_name : null,
            'printer_model' => $pos_setting ? $this->partner->posSetting->printer_model : null
        ];
    }

    private function migratePosOrderDiscounts()
    {
        $chunks = array_chunk($this->generatePartnerPosOrderDiscountsMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, ['pos_order_discounts' => $chunk]));
        }
    }


    private function generatePartnerPosOrderDiscountsMigrationData()
    {
        $this->partnerPosOrders = $this->posOrderRepository->where('partner_id', $this->partner->id)->get()->toArray();
        $partner_pos_order_ids = $this->partnerPosOrderIds = array_column($this->partnerPosOrders, 'id');
        return $this->posOrderDiscountRepository
            ->whereIn('pos_order_id', $partner_pos_order_ids)
            ->select('pos_order_id AS order_id', 'type', 'amount', 'original_amount',
                'is_percentage', 'cap', 'discount_id', 'item_id', 'created_by_name', 'updated_by_name',
                'created_at', 'updated_at')->get()->toArray();
    }

}