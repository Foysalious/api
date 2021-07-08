<?php namespace App\Sheba\Partner\DataMigration;


use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\PosOrderLog;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToPosOrderJob;
use Illuminate\Support\Facades\Redis;
use DB;
use stdClass;

class PosOrderDataMigration
{
    const CHUNK_SIZE = 10;
    private $currentQueue = 1;
    /** @var Partner */
    private $partner;
    /** @var array */
    private $partnerPosOrderIds;
    /** @var InventoryServerClient */
    private $inventoryServerClient;
    private $partnerInfo;
    private $posOrders;
    private $posOrderItems;
    private $posOrderPayments;
    private $posOrderDiscounts;
    private $posOrderLogs;


    public function __construct(InventoryServerClient $inventoryServerClient)
    {
        $this->inventoryServerClient = $inventoryServerClient;
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
        $this->generateMigrationData();
        $this->migratePartner($this->partnerInfo);
        $this->migrateOrders($this->posOrders);
    }

    private function generateMigrationData()
    {
        $this->partnerInfo = $this->generatePartnerMigrationData();
        $this->posOrders = $this->generatePosOrdersMigrationData();
        $this->posOrderItems = $this->generatePosOrderItemsData();
        $this->posOrderPayments = collect($this->generatePosOrderPaymentsData());
        $this->posOrderDiscounts = collect($this->generatePartnerPosOrderDiscountsMigrationData());
        $this->posOrderLogs = $this->generatePosOrderLogsMigrationData();
    }

    private function migratePartner($data)
    {
        $this->setRedisKey();
        dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, ['partner_info' => $data], $this->currentQueue));
        $this->increaseCurrentQueueValue();
    }

    private function migrateOrders($data)
    {
        $chunks = array_chunk($data, self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            $posOrderIds = array_column($chunk, 'id');
            list($pos_order_items, $pos_order_payments, $pos_order_discounts, $pos_order_logs) = $this->getPosOrderRelatedData($posOrderIds);
            $this->setRedisKey();
            dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, [
                'pos_orders' => $chunk,
                'pos_order_items' => $pos_order_items,
                'pos_order_payments' => $pos_order_payments,
                'pos_order_discounts' => $pos_order_discounts,
                'pos_order_logs' => $pos_order_logs

            ], $this->currentQueue));
            $this->increaseCurrentQueueValue();
        }
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

    private function generatePosOrdersMigrationData()
    {
        $pos_orders = PosOrder::where('partner_id', $this->partner->id)->where('is_migrated', '<>', 1)
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
        $pos_order_items = DB::table('pos_order_items')->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'service_id AS sku_id', 'service_name AS name', 'quantity',
                'unit_price', 'vat_percentage', 'warranty', 'warranty_unit', 'note', 'created_by_name',
                'updated_by_name', 'created_at', 'updated_at')->get();
        $service_ids = array_column($pos_order_items, 'sku_id');
        $sku_ids = $this->getSkuIdsForProducts($service_ids);
        $skus = $sku_ids['skus'];
        $pos_order_items = collect($pos_order_items);
        $pos_order_items->each(function ($item, $key) use ($skus) {
            $item->sku_id = $skus[$item->sku_id] ?? null;
        });
        return $pos_order_items;
    }

    private function generatePosOrderPaymentsData()
    {
        return DB::table('pos_order_payments')->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'amount', 'transaction_type', 'method', 'emi_month', 'interest', 
                'created_by_name', 'updated_by_name', 'created_at', 'updated_at')->get();
    }

    private function generatePartnerPosOrderDiscountsMigrationData()
    {
        return DB::table('pos_order_discounts')->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'type', 'amount', 'original_amount',
                'is_percentage', 'cap', 'discount_id', 'item_id', 'created_by_name', 'updated_by_name',
                'created_at', 'updated_at')->get();
    }

    private function generatePosOrderLogsMigrationData()
    {
        $logs =  PosOrderLog::with(['order' => function ($pos_order) {
            $pos_order->select('id');
        }])->whereIn('pos_order_id', $this->partnerPosOrderIds)->select('id','type', 'pos_order_id', 'log', 'details')->get();

        $data = collect();
         collect($logs)->each(function ($pos_order_logs) use(&$data) {
             $temp = new stdClass();
             $temp->order_id = $pos_order_logs->pos_order_id;
             $temp->old_value = json_encode([
                "log" => $pos_order_logs->log,
                "previous_order_id" => $pos_order_logs->order->previous_order_id ?: null
            ],true);
            $temp->new_value = json_encode($pos_order_logs->details,true);
            $data->push($temp);
        });
         return $data;
    }

    private function getSkuIdsForProducts($productIds)
    {
        $data = [];
        $data['product_ids'] = $productIds;
        return $this->inventoryServerClient->post('api/v1/get-skus-by-product-ids', $data);
    }

    private function getPosOrderRelatedData($orderIds): array
    {
        $pos_order_items = $this->posOrderItems->whereIn('order_id', $orderIds)->toArray();
        $pos_order_payments = $this->posOrderPayments->whereIn('order_id', $orderIds)->toArray();
        $pos_order_discounts = $this->posOrderDiscounts->whereIn('order_id', $orderIds)->toArray();
        $pos_order_logs = $this->posOrderLogs->whereIn('order_id', $orderIds)->toArray();
        return [$pos_order_items, $pos_order_payments, $pos_order_discounts, $pos_order_logs];
    }

    private function setRedisKey()
    {
        Redis::set('DataMigration::Partner::' . $this->partner->id . '::PosOrder::Queue::' . $this->currentQueue, 'initiated');
    }

    private function increaseCurrentQueueValue()
    {
        $this->currentQueue += 1;
    }

}