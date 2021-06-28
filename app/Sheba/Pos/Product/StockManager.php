<?php namespace Sheba\Pos\Product;

use App\Models\PartnerPosService;
use App\Sheba\Pos\Repositories\Interfaces\PosServiceBatchRepositoryInterface;
use Sheba\Dal\PartnerPosServiceBatch\Model as PosServiceBatch;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Pos\Repositories\PosServiceRepository;

class StockManager
{
    /** @var PosServiceRepository $serviceRepo */
    private $serviceRepo;
    /**  @var PartnerPosService $service */
    private $service;
    /** @var PosServiceBatchRepositoryInterface $partnerPosServiceBatchRepo */
    protected $partnerPosServiceBatchRepo;

    public function __construct(PosServiceRepositoryInterface $service_repo, PosServiceBatchRepositoryInterface $partnerPosServiceBatchRepo)
    {
        $this->partnerPosServiceBatchRepo = $partnerPosServiceBatchRepo;
        $this->serviceRepo = $service_repo;
    }

    public function setPosService(PartnerPosService $service)
    {
        $this->service = $service;
        return $this;
    }

    public function isStockMaintainable()
    {
        return !is_null($this->service->stock()->get()->sum('stock'));
    }

    /**
     * @param $quantity |double
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function increase($quantity)
    {
        return $this->serviceRepo->update($this->service, ['stock' => $this->service->stock + $quantity]);
    }

    /**
     * @param $quantity |double
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function decrease($quantity)
    {
        return $this->serviceRepo->update($this->service, ['stock' => $this->service->stock - $quantity]);
    }
}