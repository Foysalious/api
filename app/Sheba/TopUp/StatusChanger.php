<?php namespace Sheba\TopUp;


use App\Models\TopUpOrder;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Dal\TopupOrder\TopUpOrderRepository;

class StatusChanger
{
    /** @var TopUpOrderRepository */
    private $orderRepo;

    /** @var TopUpOrder */
    private $order;

    public function __construct(TopUpOrderRepository $order_repo)
    {
        $this->orderRepo = $order_repo;
    }

    public function setOrder(TopUpOrder $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return TopUpOrder
     */
    public function attempted()
    {
        return $this->update([
            "status" => Statuses::ATTEMPTED
        ]);
    }

    /**
     * @param $transaction_id
     * @param $transaction_details
     * @return TopUpOrder
     */
    public function pending($transaction_id, $transaction_details)
    {
        return $this->update([
            "status" => Statuses::PENDING,
            "transaction_id" => $transaction_id,
            "transaction_details" => $transaction_details,
        ]);
    }

    /**
     * @param $transaction_id
     * @param $transaction_details
     * @return TopUpOrder
     */
    public function successful($transaction_details, $transaction_id = null)
    {
        $data = [
            "status" => Statuses::SUCCESSFUL,
            "transaction_details" => $transaction_details,
        ];
        if ($transaction_id) $data["transaction_id"] = $transaction_id;

        return $this->update($data);
    }

    /**
     * @param $reason
     * @param $transaction_details
     * @return TopUpOrder
     */
    public function failed($reason, $transaction_details)
    {
        return $this->update([
            "status" => Statuses::FAILED,
            "failed_reason" => $reason,
            "transaction_details" => $transaction_details,
        ]);
    }

    /**
     * @return TopUpOrder
     */
    public function systemError()
    {
        return $this->update([
            "status" => Statuses::SYSTEM_ERROR
        ]);
    }

    /**
     * @param $data
     * @return TopUpOrder
     */
    private function update($data)
    {
        $updated_order = $this->orderRepo->update($this->order, $data);
        $this->saveLog();
        return $updated_order;
    }

    private function saveLog()
    {

    }
}
