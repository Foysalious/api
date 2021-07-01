<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PartnerPosService;
use App\Models\PosOrder;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\Dal\POSOrder\OrderStatuses;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\Pos\Log\Creator as LogCreator;
use Sheba\Pos\Log\Supported\Types;
use Sheba\Pos\Order\Creator;
use Sheba\Pos\Order\Updater;
use Sheba\Pos\Payment\Transfer as PaymentTransfer;
use Sheba\Pos\Product\StockManager;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Pos\Repositories\PosServiceRepository;

class ExchangePosItem extends RefundNature
{
    /** @var false|string */
    private $details;
    /** @var PosOrder $old_order */
    private $newOrder;
    /** @var StockManager $stockManager */
    private $stockManager;
    /** @var PosServiceRepository $serviceRepo */
    private $serviceRepo;
    /** @var PaymentTransfer $paymentTransfer */
    private $paymentTransfer;
    private $request;

    public function __construct(LogCreator $log_creator, Updater $updater, PosServiceRepositoryInterface $service_repo, StockManager $stock_manager, PaymentTransfer $transfer, Request $request)
    {
        parent::__construct($log_creator, $updater);
        $this->stockManager    = $stock_manager;
        $this->serviceRepo     = $service_repo;
        $this->paymentTransfer = $transfer;
        $this->request         = $request;
    }

    /**
     * @throws InvalidDiscountType
     * @throws ExpenseTrackingServerError
     */
    public function update()
    {
        /** @var Creator $creator */
        $creator = app(Creator::class);
        $this->stockRefill();
        $this->data['previous_order_id'] = $this->order->id;
        $this->newOrder = $creator->setPartner($this->order->partner)->setData($this->prepareCreateData())->setRequest($this->request)->setStatus(OrderStatuses::COMPLETED)->create();
        $this->generateDetails();
        $this->saveLog();
        $this->updateEntry($this->newOrder->id, 'exchange');
        $this->transferPaidAmount();
    }

    private function stockRefill()
    {
        $this->order->items->each(function ($item) {
            if ($item->service_id) {
                $partner_pos_service   = $this->serviceRepo->find($item->service_id);
                $is_stock_maintainable = $this->stockManager->setPosService($partner_pos_service)->isStockMaintainable();
                if ($is_stock_maintainable)
                    $this->stockManager->increase($item->quantity);
            }
        });
    }

    private function prepareCreateData()
    {
        $data        = $this->data;
        $services    = json_decode($data['services'], true);
        $newServices = [];
        foreach ($services as $service) {
            $item                     = $this->new ? $this->order->items()->where('id', $service['id'])->first() : $this->order->items()->where('service_id', $service['id'])->first();
            $service['id']            = $item ? $item->service_id : $service['id'];
            $service['updated_price'] = (isset($service['updated_price']) ? $service['updated_price'] : (!empty($item) ? $item->unit_price : $this->findPosServiceItem($service['id'])));
            array_push($newServices, $service);
        }
        $data['services'] = json_encode($newServices);
        return $data;
    }

    private function findPosServiceItem($id)
    {
        if (!empty($id)) {
            $service = PartnerPosService::find($id);
            return $service->price;
        }
        return 0;
    }

    /**
     * GENERATE LOG DETAILS DATA
     */
    protected function generateDetails()
    {
        $details['orders']['changes'] = [
            'new' => $this->newOrder,
            'old' => $this->order
        ];
        $this->details                = json_encode($details);
    }

    protected function saveLog()
    {
        $this->logCreator->setOrder($this->order)->setType(Types::EXCHANGE)->setLog("New Order Created for Exchange, old order id: {$this->order->id}")->setDetails($this->details)->create();
    }

    private function transferPaidAmount()
    {
        $log                        = "Transfer to " . $this->newOrder->id . " from " . $this->order->id . ", as per exchange.";
        $previous_order_paid_amount = $this->order->getPaid();
        $this->paymentTransfer->setOrder($this->newOrder)->setLog($log)->setAmount($previous_order_paid_amount)->process();
    }

    /**
     * @param PosOrder $order
     * @param $refundType
     * @throws AccountingEntryServerError
     */
    protected function updateEntry(PosOrder $order, $refundType)
    {
        $this->additionalAccountingData($order, $refundType);
        /** @var AccountingRepository $accounting_repo */
        $accounting_repo = app()->make(AccountingRepository::class);
        $this->request->merge([
            "inventory_products" => $accounting_repo->getInventoryProducts($order->items, $this->data['services']),
        ]);
        $accounting_repo->updateEntryBySource($this->request, $order->id, EntryTypes::POS);
    }

    private function additionalAccountingData(PosOrder $order, $refundType)
    {
        $this->request->merge(
            [
                "from_account_key" => (new Accounts())->asset->cash::CASH,
                "to_account_key" => (new Accounts())->income->sales::SALES_FROM_POS,
                "amount" => (double)$this->order->getNetBill(),
                "note" => $refundType,
                "source_id" => $order->id,
                "customer_id" => isset($order->customer) ? $order->customer->id : null,
                "customer_name" => isset($order->customer) ? $order->customer->name: null,
                "total_vat" => $order->getTotalVat()
            ]
        );
    }

}
