<?php namespace Sheba\Pos\Order;

use App\Models\Partner;
use App\Models\PosOrder;
use Exception;
use Illuminate\Support\Facades\DB;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\Pos\Exceptions\InvalidPosOrder;
use Sheba\Pos\Exceptions\PosExpenseCanNotBeDeleted;

class Deleter
{
    /** @var Partner $partner */
    private $partner;
    /**
     * @var PosOrder
     */
    private $order;
    private $created_at;

    public function __construct()
    {
        $this->created_at = [];
    }

    /**
     * @throws PosExpenseCanNotBeDeleted
     * @throws Exception
     */
    public function delete()
    {
        DB::transaction(function () {
            self::removePreviousOrder($this->order);
            $this->removePreviousBy();
            $expense = self::updateExpense($this->order);
            if ($expense) {
                $this->updateStock();
                $this->order->delete();
            } else {
                throw new PosExpenseCanNotBeDeleted();
            }
        });
    }

    public static function removePreviousOrder(PosOrder $order)
    {
        $previous = $order->previousOrder;
        if ($previous) {
            if ($previous->previousOrder)
                self::removePreviousOrder($previous);
            $previous->delete();
            self::updateExpense($previous);
            return $previous->id;
        }
        return null;
    }

    public static function updateExpense($order)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry = app(AutomaticEntryRepository::class);
        return $entry->setSourceId($order->id)->setSourceType(class_basename($order))->delete();
    }

    /**
     *
     */
    public function removePreviousBy()
    {
        $ids = [$this->order->id];
        if ($this->order->previousOrder) {
            array_push($ids, $this->order->previousOrder->id);
        }
        $orders = PosOrder::whereIn('previous_order_id', $ids)->get();
        foreach ($orders as $order) {
            self::updateExpense($order);
            $order->delete();
        }
    }

    public function updateStock()
    {
        $items = $this->order->items()->whereNotNull('service_id')->get();
        foreach ($items as $item) {
            if (!empty($item->service) && !is_null($item->service->stock)) {
                $item->service->stock += (int)$item->quantity;
                $item->service->save();
            }
        }
    }

    /**
     * @param mixed $order
     * @return Deleter
     * @throws InvalidPosOrder
     */
    public function setOrder($order)
    {
        if (!($order instanceof PosOrder)) {
            $order = $this->partner->posOrders()->find($order);
        }
        if (empty($order))
            throw new InvalidPosOrder();
        $this->order = $order;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return Deleter
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }


}
