<?php namespace App\Sheba\Business\Procurement;

use App\Models\Procurement;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Sheba\Business\Procurement\OrderClosedHandler;
use Sheba\Business\ProcurementStatusChangeLog\Creator;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Business\ProcurementPayment\Creator as PaymentCreator;
use DB;

class Updater
{
    private $status;
    /** @var Procurement */
    private $procurement;
    private $statusLogCreator;
    private $procurementRepository;
    private $walletTransactionHandler;
    private $paymentCreator;
    private $shebaCollection;
    private $closedAndPaidAt;
    private $data;
    private $procurementOrderCloseHandler;

    /**
     * @param mixed $closedAndPaidAt
     * @return Updater
     */
    public function setClosedAndPaidAt($closedAndPaidAt)
    {
        $this->closedAndPaidAt = $closedAndPaidAt;
        return $this;
    }


    public function __construct(ProcurementRepositoryInterface $procurement_repository, OrderClosedHandler $procurement_order_close_handler, Creator $creator, WalletTransactionHandler $wallet_transaction_handler, PaymentCreator $payment_creator)
    {
        $this->procurementRepository = $procurement_repository;
        $this->statusLogCreator = $creator;
        $this->walletTransactionHandler = $wallet_transaction_handler;
        $this->paymentCreator = $payment_creator;
        $this->data = [];
        $this->procurementOrderCloseHandler = $procurement_order_close_handler;
    }

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setShebaCollection($sheba_collection)
    {
        $this->shebaCollection = $sheba_collection;
        return $this;
    }

    public function updateStatus()
    {
        try {
            DB::transaction(function () {
                $previous_status = $this->procurement->status;
                $this->procurementRepository->update($this->procurement, ['status' => $this->status]);
                $this->statusLogCreator->setProcurement($this->procurement)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
                $this->procurement->calculate();
                if ($this->status == 'served') {
                    $this->procurementRepository->update($this->procurement, ['closed_at' => Carbon::now()]);
                    $this->procurementOrderCloseHandler->setProcurement($this->procurement->fresh())->run();
                    $this->notify();
                }
            });

        } catch (QueryException $e) {
            throw  $e;
        }
        return $this->procurement;
    }

    private function notify()
    {
        $bid = $this->procurement->getActiveBid();
        $message = $bid->bidder->name . " has served your order";
        $link = config('sheba.business_url') . '/dashboard/procurement/orders/' . $this->procurement->id . '?bid=' . $bid->id;
        foreach ($this->procurement->owner->superAdmins as $member) {
            notify()->member($member)->send([
                'title' => $message,
                'type' => 'warning',
                'event_type' => get_class($bid),
                'event_id' => $bid->id,
                'link' => $link
            ]);
        }
    }

    public function update()
    {
        $this->makeData();
        $this->procurementRepository->update($this->procurement, $this->data);
    }

    private function makeData()
    {
        $this->data['status'] = $this->status ? $this->status : $this->procurement->status;
        $this->data['sheba_collection'] = $this->shebaCollection ? $this->shebaCollection : $this->procurement->sheba_collection;
        $this->data['closed_and_paid_at'] = $this->closedAndPaidAt ? $this->closedAndPaidAt : $this->procurement->closed_and_paid_at;
    }
}
