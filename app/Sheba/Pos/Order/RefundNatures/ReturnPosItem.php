<?php

namespace Sheba\Pos\Order\RefundNatures;

use Exception;
use App\Models\PosOrder;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\Dal\POSOrder\SalesChannels as POSOrderSalesChannel;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys;
use Sheba\Pos\Log\Creator as LogCreator;
use Sheba\Pos\Order\Updater;
use Sheba\Pos\Payment\Creator as PaymentCreator;

abstract class ReturnPosItem extends RefundNature
{
    protected $details;
    protected $old_services;
    /** @var PaymentCreator $paymentCreator */
    protected $paymentCreator;
    /** @var PosOrder */
    private $oldOrder;
    protected $refundAmount = 0;
    protected $request;

    public function __construct(LogCreator $log_creator, Updater $updater, PaymentCreator $payment_creator, Request $request) {
        parent::__construct($log_creator, $updater);
        $this->paymentCreator = $payment_creator;
        $this->request = $request;
    }

    public function update()
    {
        try {
            $this->oldOrder = clone $this->order;
            $this->old_services = $this->new ? $this->order->items->pluckMultiple(['quantity', 'unit_price'], 'id', true)->toArray()
                : $this->old_services = $this->order->items->pluckMultiple(['quantity', 'unit_price'], 'service_id', true)->toArray();
            $this->makeInventoryProduct($this->order->items, $this->data['services']);
            $this->updater->setOrder($this->order)->setData($this->data)->setNew($this->new)->update();
            if ($this->order->calculate()->getPaid()) {
                $this->refundPayment();
            }
            $this->generateDetails();
            $this->saveLog();
            Log::info("checking refund 1");
            if ($this->order) {
                Log::info(["checking refund 2", $this->request->inventory_products]);
                $this->returnItem($this->order);
                $this->updateEntry($this->order, 'refund');
            }
            $this->updateIncome($this->order);
        } catch (ExpenseTrackingServerError $e) {
            app('sentry')->captureException($e);
        }
    }

    private function refundPayment()
    {
        if (isset($this->data['is_refunded']) && $this->data['is_refunded']) {
            $payment_data['pos_order_id'] = $this->order->id;
            $payment_data['amount'] = $this->data['paid_amount'];
            if ($this->data['paid_amount'] > 0) {
                $payment_data['method'] = $this->data['payment_method'];
                $this->paymentCreator->credit($payment_data);
            } else {
                $payment_data['amount'] = abs($payment_data['amount']);
                $this->paymentCreator->debit($payment_data);
            }
            $this->refundAmount = $payment_data['amount'];
        }
    }

    private function returnItem(PosOrder $order)
    {
        $amount = (double)$order->calculate()->getNetBill();
        (new JournalCreateRepository())
            ->setTypeId($order->partner->id)
            ->setSource($order)
            ->setAmount($amount)
            ->setDebitAccountKey(AccountKeys\Asset\Cash::CASH)
            ->setCreditAccountKey(AccountKeys\Income\Refund::GENERAL_REFUNDS)
            ->setDetails("Refund Pos Item")
            ->setReference("Pos Item refunds amount is" . $amount . " tk.")
            ->store();
    }

    /**
     * GENERATE LOG DETAILS DATA
     * @param null $order
     */
    protected function generateDetails($order = null)
    {
        if (isset($order) && !isset($this->oldOrder)) {
            $this->oldOrder = $order;
        }
        $changes = [];
        $this->services->each(
            function ($service) use (&$changes) {
                $changes[$service->id]['qty'] = [
                    'new' => (double)$service->quantity,
                    'old' => (double)$this->old_services[$service->id]->quantity
                ];
                $changes[$service->id]['unit_price'] = (double)$this->old_services[$service->id]->unit_price;
            }
        );
        $details['items']['changes'] = $changes;
        $details['items']['total_sale'] = $this->oldOrder->getNetBill();
        if ($this->oldOrder->sales_channel == POSOrderSalesChannel::WEBSTORE && $this->oldOrder->delivery_charge) {
            $details['items']['total_sale'] += $this->oldOrder->delivery_charge;
        }
        $details['items']['vat_amount'] = $this->oldOrder->getTotalVat();
        $details['items']['returned_amount'] = isset($this->data['paid_amount']) ? $this->data['paid_amount'] : 0.00;
        $this->details = json_encode($details);
    }

    /**
     * @param PosOrder $order
     * @throws ExpenseTrackingServerError
     */
    private function updateIncome(PosOrder $order)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry = app(AutomaticEntryRepository::class);
        $amount = (double)$order->calculate()->getNetBill();
        $entry->setPartner($order->partner)->setAmount($amount)->setAmountCleared($order->getPaid())
            ->setHead(AutomaticIncomes::POS)
            ->setSourceType(class_basename($order))
            ->setSourceId($order->id)->updateFromSrc();
    }

    /**
     * @param PosOrder $order
     * @param $refundType
     * @throws AccountingEntryServerError
     */
    protected function updateEntry(PosOrder $order, $refundType)
    {
        Log::info(['checking update data', $this->data['services'], $this->request->paid_amount, $this->request->payment_method, $this->request->refund_nature, $this->request->return_nature]);
        $this->additionalAccountingData($order, $refundType);
        /** @var AccountingRepository $accounting_repo */
        $accounting_repo = app()->make(AccountingRepository::class);
        $accounting_repo->updateEntryBySource($this->request, $order->id, EntryTypes::POS);
    }

    private function additionalAccountingData(PosOrder $order, $refundType)
    {
        $this->request->merge(
            [
                "from_account_key" => (new Accounts())->asset->cash::CASH,
                "to_account_key" => (new Accounts())->income->sales::SALES_FROM_POS,
                "amount" => isset($this->data['is_refunded']) && $this->data['is_refunded'] ? (double)$this->data['paid_amount'] : 0,
                // might have negative value
                "note" => $refundType,
                "source_id" => $order->id,
                "customer_id" => isset($order->customer) ? $order->customer->id : null,
                "customer_name" => isset($order->customer) ? $order->customer->name: null
            ]
        );
    }

    protected function makeInventoryProduct($services, $requestedServices)
    {
        Log::info("checking refund 4");
        $requested_service = json_decode($requestedServices, true);
        $inventory_products = [];
        foreach ($requested_service as $key => $value) {
            if ($services->contains($value['id'])) {
                $product = $services->find($value['id']);
                $originalSvc = $services->find($value['id'])->service;
                $sellingPrice = isset($value['updated_price']) && $value['updated_price'] ? $value['updated_price'] : $originalSvc->price;
                $unitPrice = $original_service->cost ?? $sellingPrice;
                // Full return
                if ($value['quantity'] == 0 && $product->quantity != 0) {
                    Log::info(["full return", $product->quantity]);
                    $inventory_products[] = $this->makeInventoryData(
                        $originalSvc,
                        $unitPrice,
                        $sellingPrice,
                        $product->quantity
                    );
                }
                // Quantity Increase
                if ($value['quantity'] > $product->quantity) {
                    Log::info(["quantity increase", $product->quantity]);
                    $qty = $value['quantity'] - $product->quantity;
                    $type = 'quantity_increase';
                    $inventory_products[] = $this->makeInventoryData($originalSvc, $unitPrice, $sellingPrice, $qty, $type);
                }
                // Partial Return
                if ($value['quantity'] != 0 && $value['quantity'] < $product->quantity) {
                    Log::info(["partial return", $product->quantity]);
                    $inventory_products[] = $this->makeInventoryData(
                        $originalSvc,
                        $unitPrice,
                        $sellingPrice,
                        $value['quantity']
                    );
                }
            } else {
                $original_service = ($value->service);
                if ($original_service) {
                    $sellingPrice = isset($requested_service[$key]['updated_price']) && $requested_service[$key]['updated_price'] ? $requested_service[$key]['updated_price'] : $original_service->price;
                    $unitPrice = isset($original_service->cost) ? $original_service->cost : $sellingPrice;
                    $qty = isset($requested_service[$key]['quantity']) ? $requested_service[$key]['quantity'] : 1;
                    $inventory_products[] = $this->makeInventoryData(
                        $original_service,
                        $unitPrice,
                        $sellingPrice,
                        $qty
                    );
                }
            }
        }
        $this->request->merge(
            [
                'inventory_products' => json_encode($inventory_products)
            ]
        );
    }

    private function makeInventoryData($originalSvc, $unitPrice, $sellingPrice, $quantity, $type = 'refund')
    {
        return [
            "id" => $originalSvc ? $originalSvc->id : 0,
            "name" => $originalSvc ? $originalSvc->name : 'Custom Amount',
            "unit_price" => (double)$unitPrice,
            "selling_price" => (double)$sellingPrice,
            "quantity" => $quantity,
            "type" => $type
        ];
    }
}
