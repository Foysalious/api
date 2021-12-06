<?php namespace Sheba\Pos\Order;

use App\Models\Partner;
use App\Models\PosOrder;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
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
            $expense = self::updateExpense($this->order,$this->partner);
            if ($expense) {
                $this->updateStock();
                $this->deletePosOrderEntry($this->partner, $this->order);
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

    /**
     * @param $order
     * @param Partner $partner
     * @return bool
     * @throws ExpenseTrackingServerError
     */
    public static function updateExpense($order, Partner $partner)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry = app(AutomaticEntryRepository::class);
        return $entry->setPartner($partner)->setSourceId($order->id)->setSourceType(class_basename($order))->delete();
    }

    private function deletePosOrderEntry(Partner $partner, $order)
    {
        /** @var AccountingRepository $accountingRepo */
        $accountingRepo = app(AccountingRepository::class);
        return $accountingRepo->deleteEntryBySource($partner, EntryTypes::POS, $order->id);
    }

    /**
     *
     * @throws ExpenseTrackingServerError
     */
    public function removePreviousBy()
    {
        $ids = [$this->order->id];
        if ($this->order->previousOrder) {
            array_push($ids, $this->order->previousOrder->id);
        }
        $orders = PosOrder::whereIn('previous_order_id', $ids)->get();
        foreach ($orders as $order) {
            self::updateExpense($order,$this->partner);
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
