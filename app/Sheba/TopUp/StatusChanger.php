<?php namespace Sheba\TopUp;


use App\Models\TopUpOrder;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Dal\TopupOrder\TopUpOrderRepository;
use Sheba\Dal\TopUpOrderStatusLog\TopUpOrderStatusLogRepository;

class StatusChanger
{
    /** @var TopUpOrderRepository */
    private $orderRepo;
    /** @var TopUpOrderRepository */
    private $statusRepo;

    /** @var TopUpOrder */
    private $order;
    /** @var TopUpOrder */
    private $oldOrder;

    public function __construct(TopUpOrderRepository $order_repo, TopUpOrderStatusLogRepository $status_repo)
    {
        $this->orderRepo = $order_repo;
        $this->statusRepo = $status_repo;
    }

    public function setOrder(TopUpOrder $order)
    {
        $this->order = $order;
        $this->oldOrder = clone $order;
        return $this;
    }

    /**
     * @return TopUpOrder
     */
    public function attempted()
    {
        return $this->update(Statuses::ATTEMPTED);
    }

    /**
     * @param $transaction_details
     * @param $transaction_id
     * @return TopUpOrder
     */
    public function pending($transaction_details, $transaction_id)
    {
        return $this->update(Statuses::PENDING, [
            "transaction_id" => $transaction_id,
            "transaction_details" => $transaction_details,
        ]);
    }

    /**
     * @param $transaction_details
     * @param $transaction_id
     * @return TopUpOrder
     */
    public function successful($transaction_details, $transaction_id = null)
    {
        $data = [
            "transaction_details" => $transaction_details,
        ];
        if ($transaction_id) $data["transaction_id"] = $transaction_id;

        return $this->update(Statuses::SUCCESSFUL, $data);
    }

    /**
     * @param $reason
     * @param $transaction_details
     * @return TopUpOrder
     */
    public function failed($reason, $transaction_details)
    {
        return $this->update(Statuses::FAILED, [
            "failed_reason" => $reason,
            "transaction_details" => $transaction_details,
        ]);
    }

    /**
     * @return TopUpOrder
     */
    public function systemError()
    {
        return $this->update( Statuses::SYSTEM_ERROR);
    }

    /**
     * @param $status
     * @param $data
     * @return TopUpOrder
     */
    private function update($status, $data = [])
    {
        DB::transaction(function () use ($data, $status) {
            $data["status"] = $status;
            $this->orderRepo->update($this->order, $data);
            $this->saveLog();
        });

        $this->oldOrder = clone $this->order;
        return $this->order;
    }

    private function saveLog()
    {
        $this->statusRepo->create([
            "topup_order_id" => $this->order->id,
            "from" => $this->oldOrder->status,
            "to" => $this->order->status,
            "transaction_details" => $this->oldOrder->transaction_details
        ]);
    }
}
