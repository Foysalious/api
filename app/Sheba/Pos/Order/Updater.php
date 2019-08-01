<?php namespace Sheba\Pos\Order;

use App\Models\PosOrder;
use App\Models\PosOrderItem;
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

    public function __construct(PosOrderRepository $order_repo, PosOrderItemRepository $item_repo,
                                PosServiceRepositoryInterface $service_repo, StockManager $stock_manager)
    {
        $this->orderRepo = $order_repo;
        $this->itemRepo = $item_repo;
        $this->serviceRepo = $service_repo;
        $this->stockManager = $stock_manager;
    }

    public function setOrder(PosOrder $order)
    {
        $this->order = $order;
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
                $item = $this->itemRepo->findByService($this->order, $service['id']);
                $service_data['quantity'] = $service['quantity'];
                $this->manageStock($item, $service['id'], $service['quantity']);
                $this->itemRepo->update($item, $service_data);
            }
        }
    }

    /**
     * @param PosOrderItem $item
     * @param $service_id
     * @param $service_quantity
     */
    public function manageStock(PosOrderItem $item, $service_id, $service_quantity)
    {
        $partner_pos_service = $this->serviceRepo->find($service_id);
        $is_stock_maintainable = $this->stockManager->setPosService($partner_pos_service)->isStockMaintainable();
        if ($is_stock_maintainable) {
            $changed_quantity = abs($service_quantity - $item->quantity);
            if ($item->quantity > $service_quantity) $this->stockManager->increase($changed_quantity);
            elseif ($item->quantity < $service_quantity) $this->stockManager->decrease($changed_quantity);
        }
    }
}