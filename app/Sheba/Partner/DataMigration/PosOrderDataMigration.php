<?php namespace App\Sheba\Partner\DataMigration;


use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\PosOrderLog;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToPosOrderJob;
use Illuminate\Support\Facades\Redis;
use DB;
use Sheba\Pos\Repositories\PosOrderPaymentRepository;
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
    private $posCustomers;


    public function __construct(InventoryServerClient $inventoryServerClient, PosOrderPaymentRepository $orderPaymentRepository)
    {
        $this->inventoryServerClient = $inventoryServerClient;
        $this->orderPaymentRepository = $orderPaymentRepository;
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
        $this->posCustomers = collect($this->generatePosCustomersData());
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
            $posCustomerIds = array_column($chunk, 'customer_id');
            list($pos_order_items, $pos_order_payments, $pos_order_discounts, $pos_order_logs, $pos_customers) = $this->getPosOrderRelatedData($posOrderIds, $posCustomerIds);
            $this->setRedisKey();
            dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, [
                'pos_orders' => $chunk,
                'pos_order_items' => $pos_order_items,
                'pos_order_payments' => $pos_order_payments,
                'pos_order_discounts' => $pos_order_discounts,
                'pos_order_logs' => $pos_order_logs,
                'pos_customers' => $pos_customers
            ], $this->currentQueue));
            $this->increaseCurrentQueueValue();
        }
    }

    private function generatePartnerMigrationData()
    {
        $pos_setting = $this->partner->posSetting;
        return [
            'id' => $this->partner->id,
            'name' => $this->partner->name,
            'sub_domain' => $this->partner->sub_domain,
            'sms_invoice' => $pos_setting ? $this->partner->posSetting->sms_invoice : 0,
            'auto_printing' => $pos_setting ? $this->partner->posSetting->auto_printing : 0,
            'printer_name' => $pos_setting ? $this->partner->posSetting->printer_name : null,
            'printer_model' => $pos_setting ? $this->partner->posSetting->printer_model : null,
            'created_at' => $this->partner->created_at->format('Y-m-d H:i:s'),
            'created_by_name' => $this->partner->created_by_name,
            'updated_at' => $this->partner->updated_at->format('Y-m-d H:i:s'),
            'updated_by_name' => $this->partner->updated_by_name,
        ];
    }

    private function generatePosOrdersMigrationData()
    {
        $pos_orders = PosOrder::where('partner_id', $this->partner->id)->where(function ($q) {
            $q->where('is_migrated', null)->orWhere('is_migrated', 0);
        })->withTrashed()
            ->leftJoin('pos_order_payments', 'pos_orders.id', '=', 'pos_order_payments.pos_order_id')
            ->leftJoin('pos_customers', 'pos_orders.customer_id', '=', 'pos_customers.id')
            ->leftJoin('profiles', 'profiles.id', '=', 'pos_customers.profile_id')
            ->select('pos_orders.id', 'pos_orders.partner_wise_order_id', 'pos_orders.partner_id', 'pos_orders.customer_id', DB::raw('(CASE 
                        WHEN pos_orders.payment_status = "Paid" THEN pos_order_payments.created_at
                        ELSE NULL 
                        END) AS paid_at'), DB::raw('(CASE 
                        WHEN pos_orders.sales_channel = "pos" THEN "1" 
                        ELSE "2" 
                        END) AS sales_channel_id'), 'pos_orders.emi_month',
                'pos_orders.bank_transaction_charge', 'pos_orders.interest', 'pos_orders.delivery_charge',
                'pos_orders.address AS delivery_address', 'pos_orders.delivery_vendor_name', 'pos_orders.delivery_request_id',
                'pos_orders.delivery_thana', 'pos_orders.delivery_district', 'pos_orders.note', 'pos_orders.status',
                'pos_orders.voucher_id', 'pos_orders.created_at', 'pos_orders.created_by_name', 'pos_orders.updated_at',
                'pos_orders.updated_by_name', 'pos_orders.deleted_at', 'profiles.name AS delivery_name', 'profiles.mobile AS delivery_mobile')->groupBy('id')->get()->toArray();
        $this->partnerPosOrderIds = array_column($pos_orders, 'id');

        return $pos_orders;
    }

    private function generatePosOrderItemsData()
    {
        $pos_order_items = DB::table('pos_order_items')->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'service_id AS sku_id', 'service_name AS name', 'quantity',
                'unit_price', 'vat_percentage', 'warranty', 'warranty_unit', 'note', 'created_by_name',
                'updated_by_name', 'created_at', 'updated_at')->get();
        $service_ids = array_column($pos_order_items->toArray(), 'sku_id');
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
        $pos_order_payments = DB::table('pos_order_payments')->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'amount', 'transaction_type', 'method', 'method_details', 'emi_month', 'interest',
                'created_by_name', 'updated_by_name', 'created_at', 'updated_at')->get();
        $collection = collect($pos_order_payments);
        $cash_details = json_encode(['payment_method_en' => 'Cash', 'payment_method_bn' => ' নগদ গ্রহন', 'payment_method_icon' => config('s3.url') . 'pos/payment/cash_v2.png']);
        $digital_payment_details = json_encode(['payment_method_en' => 'Digital Payment', 'payment_method_bn' => 'ডিজিটাল পেমেন্ট', 'payment_method_icon' => config('s3.url') . 'pos/payment/digital_collection_v2.png']);
        $other_details = json_encode(['payment_method_en' => 'Others', 'payment_method_bn' => 'অন্যান্য', 'payment_method_icon' => config('s3.url') . 'pos/payment/others_v2.png']);
        $payments = $collection->map(function($payment) use ($cash_details, $digital_payment_details, $other_details) {
            if ($payment->method == 'advance_balance' || $payment->method == 'cod') return [
                "order_id" => $payment->order_id,
                "amount" => $payment->amount,
                "transaction_type" => $payment->transaction_type,
                "method" => $payment->method,
                "method_details" => $cash_details,
                "emi_month" => $payment->emi_month,
                "interest" => $payment->interest,
                "created_by_name" => $payment->created_by_name,
                "updated_by_name" => $payment->updated_by_name,
                "created_at" => $payment->created_at,
                "updated_at" => $payment->updated_at,
            ];
            if ($payment->method == 'later' || $payment->method == 'transfer' || $payment->method == 'others') return [
                "order_id" => $payment->order_id,
                "amount" => $payment->amount,
                "transaction_type" => $payment->transaction_type,
                "method" => $payment->method,
                "method_details" => $other_details,
                "emi_month" => $payment->emi_month,
                "interest" => $payment->interest,
                "created_by_name" => $payment->created_by_name,
                "updated_by_name" => $payment->updated_by_name,
                "created_at" => $payment->created_at,
                "updated_at" => $payment->updated_at,
            ];
            return [
                "order_id" => $payment->order_id,
                "amount" => $payment->amount,
                "transaction_type" => $payment->transaction_type,
                "method" => $payment->method,
                "method_details" => $digital_payment_details,
                "emi_month" => $payment->emi_month,
                "interest" => $payment->interest,
                "created_by_name" => $payment->created_by_name,
                "updated_by_name" => $payment->updated_by_name,
                "created_at" => $payment->created_at,
                "updated_at" => $payment->updated_at,
            ];
        });
        return $payments->toArray();
    }

    private function generatePartnerPosOrderDiscountsMigrationData()
    {
        return DB::table('pos_order_discounts')->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'type', 'amount', 'original_amount',
                'is_percentage', 'cap', 'discount_id', 'item_id AS type_id', 'created_by_name', 'updated_by_name',
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

    public function generatePosCustomersData()
    {
        return DB::table('partner_pos_customers')
            ->where('partner_id', $this->partner->id)
            ->leftJoin('pos_customers', 'partner_pos_customers.customer_id', '=', 'pos_customers.id')
            ->leftJoin('profiles', 'pos_customers.profile_id', '=', 'profiles.id')
            ->select('partner_pos_customers.customer_id as id', 'partner_pos_customers.partner_id', 'profiles.name',
                'profiles.mobile', 'profiles.email', 'profiles.pro_pic', 'profiles.created_at', 'profiles.updated_at')->get();
    }

    private function getSkuIdsForProducts($productIds)
    {
        $data = [];
        $data['product_ids'] = $productIds;
        return $this->inventoryServerClient->post('api/v1/get-skus-by-product-ids', $data);
    }

    private function getPosOrderRelatedData($orderIds, $posCustomerIds): array
    {
        $pos_order_items = $this->posOrderItems->whereIn('order_id', $orderIds)->toArray();
        $pos_order_payments = $this->posOrderPayments->whereIn('order_id', $orderIds)->toArray();
        $pos_order_discounts = $this->posOrderDiscounts->whereIn('order_id', $orderIds)->toArray();
        $pos_order_logs = $this->posOrderLogs->whereIn('order_id', $orderIds)->toArray();
        $pos_customers = $this->posCustomers->whereIn('id', $posCustomerIds)->toArray();
        return [$pos_order_items, $pos_order_payments, $pos_order_discounts, $pos_order_logs, $pos_customers];
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