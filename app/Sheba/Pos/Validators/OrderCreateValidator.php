<?php namespace Sheba\Pos\Validators;

use Sheba\Pos\Product\StockManager;
use Sheba\Pos\Repositories\PosServiceRepository;

class OrderCreateValidator extends Validator
{
    /** @var StockManager $stockManager */
    private $stockManager;
    /** @var PosServiceRepository $posServiceRepo */
    private $posServiceRepo;

    public function __construct(StockManager $stock_manager, PosServiceRepository $pos_service_repo)
    {
        $this->stockManager = $stock_manager;
        $this->posServiceRepo = $pos_service_repo;
    }

    /** @var array $services */
    private $services;

    public function hasError()
    {
        if ($this->isOutOfStock()) return ['code' => 421, 'msg' => 'Product out of stock.'];
    }

    /**
     * @param array $services
     */
    public function setServices(array $services)
    {
        $this->services = $services;
    }

    private function isOutOfStock()
    {
        $is_out_of_stock = false;
        foreach ($this->services as $service) {
            $original_service = $this->posServiceRepo->find($service['id']);
            $is_stock_maintainable = $this->stockManager->setPosService($original_service)->isStockMaintainable();
            if ($is_stock_maintainable) {
                $is_out_of_stock = $service['quantity'] > $original_service->stock;
                if ($is_out_of_stock) return true;
            }
        }
    }
}