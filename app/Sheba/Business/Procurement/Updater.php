<?php namespace App\Sheba\Business\Procurement;

use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\ProcurementItemField;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Sheba\Business\Procurement\OrderClosedHandler;
use Sheba\Business\Procurement\RequestHandler;
use Sheba\Business\ProcurementStatusChangeLog\Creator;
use Sheba\Repositories\Interfaces\ProcurementItemFieldRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementItemRepositoryInterface;
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
    private $procurementItemFieldData;
    private $procurementItemRepository;
    private $procurementItemFieldRepository;

    /** @var RequestHandler $requestHandler */
    private $requestHandler;

    /**
     * Updater constructor.
     * @param ProcurementRepositoryInterface $procurement_repository
     * @param OrderClosedHandler $procurement_order_close_handler
     * @param Creator $creator
     * @param WalletTransactionHandler $wallet_transaction_handler
     * @param PaymentCreator $payment_creator
     * @param ProcurementItemRepositoryInterface $procurement_item_repository
     * @param ProcurementItemFieldRepositoryInterface $procurement_item_field_repository
     */
    public function __construct(ProcurementRepositoryInterface $procurement_repository,
                                OrderClosedHandler $procurement_order_close_handler,
                                Creator $creator,
                                WalletTransactionHandler $wallet_transaction_handler,
                                PaymentCreator $payment_creator,
                                ProcurementItemRepositoryInterface $procurement_item_repository,
                                ProcurementItemFieldRepositoryInterface $procurement_item_field_repository)
    {
        $this->procurementRepository = $procurement_repository;
        $this->statusLogCreator = $creator;
        $this->walletTransactionHandler = $wallet_transaction_handler;
        $this->paymentCreator = $payment_creator;
        $this->data = [];
        $this->procurementOrderCloseHandler = $procurement_order_close_handler;
        $this->procurementItemRepository = $procurement_item_repository;
        $this->procurementItemFieldRepository = $procurement_item_field_repository;
    }

    /**
     * @param RequestHandler $request_handler
     * @return $this
     */
    public function setRequestHandler(RequestHandler $request_handler)
    {
        $this->requestHandler = $request_handler;
        return $this;
    }

    /**
     * @param Procurement $procurement
     * @return $this
     */
    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param $sheba_collection
     * @return $this
     */
    public function setShebaCollection($sheba_collection)
    {
        $this->shebaCollection = $sheba_collection;
        return $this;
    }

    /**
     * @param mixed $closedAndPaidAt
     * @return Updater
     */
    public function setClosedAndPaidAt($closedAndPaidAt)
    {
        $this->closedAndPaidAt = $closedAndPaidAt;
        return $this;
    }

    public function update()
    {
        $this->makeData();
        $this->procurementRepository->update($this->procurement, $this->data);
    }

    private function makeData()
    {
        $this->data['long_description'] = $this->requestHandler->getLongDescription() ? $this->requestHandler->getLongDescription() : $this->procurement->long_description;
        $this->data['number_of_participants'] = $this->requestHandler->getNumberOfParticipants() || $this->requestHandler->getNumberOfParticipants() == 0 ? $this->requestHandler->getNumberOfParticipants() : $this->procurement->number_of_participants;
        $this->data['last_date_of_submission'] = $this->requestHandler->getLastDateOfSubmission() ? $this->requestHandler->getLastDateOfSubmission() : $this->procurement->last_date_of_submission;
        $this->data['procurement_start_date'] = $this->requestHandler->getProcurementStartDate() ? $this->requestHandler->getProcurementStartDate() : $this->procurement->procurement_start_date;
        $this->data['procurement_end_date'] = $this->requestHandler->getProcurementEndDate() ? $this->requestHandler->getProcurementEndDate() : $this->procurement->procurement_end_date;
        $this->data['payment_options'] = $this->requestHandler->getPaymentOptions() ? $this->requestHandler->getPaymentOptions() : null;

        $this->data['status'] = $this->status ? $this->status : $this->procurement->status;
        $this->data['sheba_collection'] = $this->shebaCollection ? $this->shebaCollection : $this->procurement->sheba_collection;
        $this->data['closed_and_paid_at'] = $this->closedAndPaidAt ? $this->closedAndPaidAt : $this->procurement->closed_and_paid_at;
    }

    /**
     * @param ProcurementItem $procurement_item
     * @param $fields
     */
    public function itemFieldsUpdate(ProcurementItem $procurement_item, $fields)
    {
        DB::beginTransaction();
        try {
            $this->procurementItemFieldData = [];
            $procurement_item->fields->each(function ($field) {
                $this->itemFieldsDelete($field);
            });
            $this->makeItemFields($procurement_item, $fields);
            $this->procurementItemFieldRepository->createMany($this->procurementItemFieldData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    /**
     * @param ProcurementItem $procurement_item
     * @param $fields
     */
    private function makeItemFields(ProcurementItem $procurement_item, $fields)
    {
        foreach ($fields as $field) {
            $is_required = isset($field['is_required']) ? $field['is_required'] : 1;
            $options = isset($field['options']) ? $field['options'] : [];
            $unit = isset($field['unit']) ? $field['unit'] : null;
            array_push($this->procurementItemFieldData, [
                'title' => $field['title'],
                'short_description' => isset($field['short_description']) ? $field['short_description'] : '',
                'input_type' => isset($field['type']) ? $field['type'] : null,
                'result' => isset($field['result']) ? $field['result'] : null,
                'procurement_item_id' => $procurement_item->id,
                'variables' => json_encode(['is_required' => $is_required, 'options' => $options, 'unit' => $unit])
            ]);
        }
    }

    /**
     * @param ProcurementItemField $field
     */
    private function itemFieldsDelete(ProcurementItemField $field)
    {
        $this->procurementItemFieldRepository->delete($field->id);
    }

    /**
     * @return Procurement
     */
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
}
