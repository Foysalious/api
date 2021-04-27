<?php namespace App\Sheba\Partner\DataMigration;


use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\PosOrderLog;
use App\Models\PosOrderPayment;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToPosOrderJob;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Sheba\Pos\Repositories\Interfaces\PosDiscountRepositoryInterface;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Pos\Repositories\PosOrderLogRepository;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Repositories\PartnerRepository;
use DB;

class PosOrderDataMigration
{
    const CHUNK_SIZE = 10;
    /** @var Partner */
    private $partner;
    /** @var PosOrderServerClient */
    private $client;
    /** @var PartnerRepository */
    private $partnerRepository;
    /** @var PosDiscountRepositoryInterface  */
    private $posOrderDiscountRepository;
    /** @var PosOrderRepository */
    private $posOrderRepository;
    /** @var array */
    private $partnerPosOrderIds;
    /** @var PosOrderItemRepository */
    private $posOrderItemRepository;
    /** @var InventoryServerClient */
    private $inventoryServerClient;
    /**
     * @var PosOrderLogRepository
     */
    private $posOrderLogRepository;



    public function __construct(PartnerRepository $partnerRepository,
                                PosOrderRepository $posOrderRepository,
                                PosOrderLogRepository $posOrderLogRepository,
                                PosDiscountRepositoryInterface $posOrderDiscountRepository,
                                PosOrderServerClient $client,
                                PosOrderItemRepository $posOrderItemRepository,
                                InventoryServerClient $inventoryServerClient)
    {
        $this->partnerRepository = $partnerRepository;
        $this->posOrderRepository = $posOrderRepository;
        $this->posOrderDiscountRepository = $posOrderDiscountRepository;
        $this->client = $client;
        $this->posOrderItemRepository = $posOrderItemRepository;
        $this->inventoryServerClient = $inventoryServerClient;
        $this->posOrderLogRepository = $posOrderLogRepository;
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
        $this->migrateOrders();
        $this->migrateOrderSkus();
        $this->migrateOrderPayments();
        $this->migratePosOrderDiscounts();
        $this->migratePosOrderLogs();
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

    private function migrateOrders()
    {
        $chunks = array_chunk($this->generatePosOrdersMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, ['pos_orders' => $chunk]));
        }
    }

    private function migrateOrderSkus()
    {
        $chunks = array_chunk($this->generatePosOrderItemsData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, ['pos_order_items' => $chunk]));
        }
    }

    private function migrateOrderPayments()
    {
        $chunks = array_chunk($this->generatePosOrderPaymentsData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, ['pos_order_payments' => $chunk]));
        }
    }

    private function migratePosOrderDiscounts()
    {
        $pos_orders = PosOrder::where('partner_id', $this->partner->id)
            ->select('id', 'partner_wise_order_id', 'partner_id', 'customer_id', 'sales_channel', 'emi_month',
                'bank_transaction_charge', 'interest', 'delivery_charge', 'address AS delivery_address', 'note',
                'voucher_id')->get()->toArray();
        $this->partnerPosOrderIds = array_column($pos_orders, 'id');
        $chunks = array_chunk($this->generatePartnerPosOrderDiscountsMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, ['pos_order_discounts' => $chunk]));
        }
    }

    private function generatePosOrdersMigrationData()
    {
        $pos_orders = PosOrder::where('partner_id', $this->partner->id)
            ->select('id', 'partner_wise_order_id', 'partner_id', 'customer_id', DB::raw('(CASE 
                        WHEN pos_orders.sales_channel = "pos" THEN "1" 
                        ELSE "2" 
                        END) AS sales_channel_id'), 'emi_month',
                'bank_transaction_charge', 'interest', 'delivery_charge', 'address AS delivery_address', 'note',
                'voucher_id')->get()->toArray();
        $this->partnerPosOrderIds = array_column($pos_orders, 'id');
        return $pos_orders;
    }

    private function generatePosOrderItemsData()
    {
        $pos_order_items = $this->posOrderItemRepository->getModel()->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'service_id AS sku_id', 'service_name AS name', 'quantity',
                'unit_price', 'vat_percentage', 'warranty', 'warranty_unit', 'note', 'created_by_name',
                'updated_by_name', 'created_at', 'updated_at')->get();
        $service_ids = array_column($pos_order_items->toArray(), 'sku_id');
        $sku_ids = $this->getSkuIdsForProducts($service_ids);
        $skus = $sku_ids['skus'];
        $pos_order_items->each(function ($item, $key) use ($skus) {
            $item['sku_id'] = isset($skus[$item['sku_id']]) ? $skus[$item['sku_id']] : null;
        });
        return $pos_order_items->toArray();
    }

    private function generatePosOrderPaymentsData()
    {
        return PosOrderPayment::whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'amount', 'transaction_type', 'method', 'emi_month', 'interest', 
                'created_by_name', 'updated_by_name', 'created_at', 'updated_at')->get()->toarray();
    }

    private function generatePartnerPosOrderDiscountsMigrationData()
    {
        return $this->posOrderDiscountRepository
            ->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'type', 'amount', 'original_amount',
                'is_percentage', 'cap', 'discount_id', 'item_id', 'created_by_name', 'updated_by_name',
                'created_at', 'updated_at')->get()->toArray();
    }

    private function getSkuIdsForProducts($productIds)
    {
        $data = [];
        $data['product_ids'] = $productIds;
        return $this->inventoryServerClient->post('api/v1/get-skus-by-product-ids', $data);
    }

    private function migratePosOrderLogs()
    {
        $chunks = array_chunk($this->generatePosOrderLogsMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, ['pos_order_logs' => $chunk]));
        }

    }

    private function generatePosOrderLogsMigrationData()
    {
        $pos_orders = PosOrder::where('partner_id', $this->partner->id)
            ->select('id', 'partner_wise_order_id', 'partner_id', 'customer_id', 'sales_channel', 'emi_month',
                'bank_transaction_charge', 'interest', 'delivery_charge', 'address AS delivery_address', 'note',
                'voucher_id')->get()->toArray();
        $this->partnerPosOrderIds = array_column($pos_orders, 'id');

        $logs =  PosOrderLog::with(['order' => function ($pos_order) {
            $pos_order->select('id');
        }])->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('id','type', 'pos_order_id', 'log', 'details')
            ->get();

        $data = [];
         collect($logs)->each(function ($pos_order_logs) use(&$data) {
            $temp['order_id'] = $pos_order_logs->pos_order_id;
            $temp['old_value'] = json_encode([
                "log" => $pos_order_logs->log,
                "previous_order_id" => $pos_order_logs->order->previous_order_id ?: null
            ],true);
            $temp['new_value'] = json_encode($pos_order_logs->details,true);
            array_push($data,$temp) ;
        });
         return $data;
    }

}