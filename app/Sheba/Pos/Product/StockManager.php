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
        if (is_null($this->service->id)) return false;
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
     * @return array
     */
    public function decrease($quantity)
    {
        return $this->updatedStock($quantity);
    }

    private function updatedStock($quantity) : array
    {
        if($this->service->partner->isMigratedToAccounting) {
            $decreasedBatchesInfo = [];
            while ($quantity > 0) {
                $firstBatch = PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->first();
                $allBatches = PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->get();
                if($quantity >= $firstBatch->stock && count($allBatches) > 1) {
                    $decreasedBatchesInfo[$firstBatch->id] = ['stock' => $firstBatch->stock, 'cost' => $firstBatch->cost];
                    $quantity =  $quantity - $firstBatch->stock;
                    $firstBatch->update(['stock' => 0 ]);
                    $firstBatch->delete();
                } else if($quantity >= $firstBatch->stock && count($allBatches) == 1) {
                    $negativeStock = $firstBatch->stock - $quantity;
                    $decreasedBatchesInfo[$firstBatch->id] = ['stock' => $quantity, 'cost' => $firstBatch->cost];
                    $firstBatch->update(['stock' => $negativeStock ]);
                    $quantity = 0;
                } else {
                    $decreasedBatchesInfo[$firstBatch->id] = ['stock' => $quantity, 'cost' => $firstBatch->cost];
                    $firstBatch->update(['stock' => $firstBatch->stock - $quantity]);
                    $quantity = 0;
                }
            }
            return $decreasedBatchesInfo;
        }
        $current_stock = $this->service->stock - $quantity;
        $this->serviceRepo->update($this->service, ['stock' => $current_stock]);
        return $decreasedBatchesInfo[0] = ['stock' => $current_stock, 'cost' => $this->service->cost];


    }

    private function increaseUpdatedStock($quantity)
    {
        if($this->service->partner->isMigratedToAccounting) {
            $lastBatch = PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->latest()->first();
            $lastStock = $lastBatch ? $lastBatch->stock : 0;
            PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->where('id', $lastBatch->id)->update(['stock' => $lastStock + $quantity]);
            return $lastStock + $quantity;
        }
        return $this->service->stock + $quantity;

    }
}