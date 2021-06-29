<?php namespace Sheba\Pos\Product;

use App\Models\PartnerPosService;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Pos\Repositories\PosServiceRepository;

class StockManager
{
    /** @var PosServiceRepository $serviceRepo */
    private $serviceRepo;
    /**  @var PartnerPosService $service */
    private $service;

    public function __construct(PosServiceRepositoryInterface $service_repo)
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
        return !is_null($this->service->getStock());
    }

    /**
     * @param $quantity |double
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function increase($quantity)
    {
        return $this->serviceRepo->update($this->service, ['stock' => 10]);
    }

    /**
     * @param $quantity |double
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function decrease($quantity)
    {
        return $this->serviceRepo->update($this->service, ['stock' => 10]);
    }
}