<?php namespace Sheba\Pos\Order;

use App\Exceptions\Pos\Order\NotEnoughStockException;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\Pos\Discount\Handler as DiscountHandler;
use Sheba\Pos\Product\StockManager;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Pos\Repositories\PosServiceRepository;

class Updater
{
    /** @var PosOrder $order */
    private $order;
    /** @var array $data */
    private $data;
    /** @var PosOrderItemRepository $itemRepo */
    private $itemRepo;
    /** @var PosOrderRepository */
    private $orderRepo;
    /** @var PosServiceRepository $serviceRepo */
    private $serviceRepo;
    /** @var StockManager $stockManager */
    private $stockManager;
    /** @var DiscountHandler $discountHandler */
    private $discountHandler;
    private $new;

    public function __construct(PosOrderRepository $order_repo, PosOrderItemRepository $item_repo, PosServiceRepositoryInterface $service_repo, StockManager $stock_manager, DiscountHandler $discount_handler)
    {
        $this->orderRepo       = $order_repo;
        $this->itemRepo        = $item_repo;
        $this->serviceRepo     = $service_repo;
        $this->stockManager    = $stock_manager;
        $this->discountHandler = $discount_handler;
    }

    /**
     * @param mixed $new
     * @return Updater
     */
    public function setNew($new)
    {
        $this->new = $new;
        return $this;
    }

    public function setOrder(PosOrder $order)
    {
        $this->order = $order->calculate();
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function update()
    {
        if (isset($this->data['services'])) {
            $services = json_decode($this->data['services'], true);
            foreach ($services as $service) {
                $item = $this->new ? $this->itemRepo->findFromOrder($this->order, $service['id']) : $this->itemRepo->findByService($this->order, $service['id']);
                $service_data['quantity'] = $service['quantity'];
                if ($item->discount && $item->quantity) {
                    $service_discount_data['amount'] = ($item->discount->amount / $item->quantity) * $service['quantity'];
                    $this->discountHandler->setDiscount($item->discount)->setServiceDiscountData($service_discount_data)->update();
                }
                $this->manageStock($item, $item->service_id, $service['quantity']);
                $this->itemRepo->update($item, $service_data);
            }
        }
        $order_data = [];
        if (isset($this->data['customer_id']))
            $order_data['customer_id'] = $this->data['customer_id'];
        /** @var PosOrder $order */
        $order = $this->orderRepo->update($this->order, $order_data);
        return $order;
    }

    /**
     * @param PosOrderItem $item
     * @param $service_id
     * @param $service_quantity
     */
    public function manageStock(PosOrderItem $item, $service_id, $service_quantity)
    {
        if (!$service_id) return;
        $partner_pos_service = $this->serviceRepo->find($service_id);
        if (empty($partner_pos_service))
            return;
        $is_stock_maintainable = $this->stockManager->setPosService($partner_pos_service)->isStockMaintainable();
        if ($is_stock_maintainable) {
            if ($item->service->is_published_for_shop  && $service_quantity > $item->service->getStock())
                throw new NotEnoughStockException("Not enough stock", 403);
            $changed_quantity = abs($service_quantity - $item->quantity);
            if ($item->quantity > $service_quantity)
                $this->stockManager->increase($changed_quantity);
            elseif ($item->quantity < $service_quantity)
                $this->stockManager->decrease($changed_quantity);
        }
    }


}
