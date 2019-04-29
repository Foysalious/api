<?php namespace Sheba\Pos\Product;

use App\Models\PartnerPosService;
use Sheba\Pos\Repositories\PosServiceRepository;

class StockManager
{
    /** @var PosServiceRepository $serviceRepo */
    private $serviceRepo;
    /**  @var PartnerPosService $service */
    private $service;

    public function __construct(PosServiceRepository $service_repo)
    {
        $this->serviceRepo = $service_repo;
    }

    public function setPosService(PartnerPosService $service)
    {
        $this->service = $service;
        return $this;
    }

    public function isStockMaintainable()
    {
        return !is_null($this->service->stock);
    }

    /**
     * @param $quantity |double
     * @return bool|int
     */
    public function increase($quantity)
    {
        return $this->serviceRepo->update($this->service, ['stock' => $this->service->stock + $quantity]);
    }

    /**
     * @param $quantity |double
     * @return bool|int
     */
    public function decrease($quantity)
    {
        return $this->serviceRepo->update($this->service, ['stock' => $this->service->stock - $quantity]);
    }
}