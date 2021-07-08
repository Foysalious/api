<?php namespace Sheba\Pos\Product;

use App\Models\PartnerPosService;
use Sheba\Dal\PartnerPosServiceBatch\Model as PartnerPosServiceBatch;
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
        return $this->serviceRepo->update($this->service, ['stock' => $this->increaseUpdatedStock($quantity)]);
    }

    /**
     * @param $quantity |double
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function decrease($quantity)
    {
        return $this->serviceRepo->update($this->service, ['stock' => $this->updatedStock($quantity)]);
    }

    private function updatedStock($quantity)
    {
        while ($quantity > 0) {

            $firstBatch = PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->first();
            $allBatches = PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->get();

            if($quantity >= $firstBatch->stock && count($allBatches) > 1) {
                $quantity =  $quantity - $firstBatch->stock;
                $firstBatch->delete();
            }
            else if($quantity >= $firstBatch->stock && count($allBatches) == 1) {
                $negativeStock = $firstBatch->stock - $quantity;
                $firstBatch->update(['stock' => $negativeStock ]);
                $quantity = 0;
            }
            else {
                $firstBatch->update(['stock' => $firstBatch->stock - $quantity]);
                $quantity = 0;
            }
        }
        return true;
    }

    private function increaseUpdatedStock($quantity)
    {
        $lastBatch = PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->latest()->first();
        $lastStock = $lastBatch ? $lastBatch->stock : 0;
        PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->where('id', $lastBatch->id)->update(['stock' => $lastStock + $quantity]);
        return $lastStock + $quantity;
    }
}