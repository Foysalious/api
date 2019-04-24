<?php namespace Sheba\Pos\Order;

use App\Models\PartnerPosService;
use App\Models\PosOrder;
use Sheba\Pos\Repositories\PosOrderItemRepository;

class Updater
{
    /**
     * @var PosOrder
     */
    private $order;
    /**
     * @var array
     */
    private $data;
    /** @var PosOrderItemRepository $itemRepo */
    private $itemRepo;

    public function __construct(PosOrderItemRepository $item_repo)
    {
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
        $services = json_decode($this->data['services'], true);
        foreach ($services as $service) {
            $item = $this->itemRepo->findByService($this->order, $service['id']);
            $service_data['quantity'] = $service['quantity'];
            $this->itemRepo->update($item, $service_data);
        }
    }
}