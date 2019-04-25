<?php namespace Sheba\Pos\Order;

use App\Models\PosOrder;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Pos\Repositories\PosOrderRepository;

class Updater
{
    /** @var PosOrder $order*/
    private $order;
    /** @var array $data*/
    private $data;
    /** @var PosOrderItemRepository $itemRepo */
    private $itemRepo;
    /** @var PosOrderRepository */
    private $orderRepo;

    public function __construct(PosOrderRepository $order_repo, PosOrderItemRepository $item_repo)
    {
        $this->orderRepo = $order_repo;
        $this->itemRepo = $item_repo;
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
                $this->itemRepo->update($item, $service_data);
            }
        }
    }
}