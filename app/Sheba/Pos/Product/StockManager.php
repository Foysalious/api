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
        $lastStock = 0;
        while ($quantity > 0) {

            $lastBatch = PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->latest()->first();
            $allBatches = PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->get();
            if(!$lastBatch) $lastStock = 0;
            else $lastStock = $lastBatch->stock;

            if($lastStock < $quantity && count($allBatches) == 1) {
                $lastStock -= $quantity;
                $quantity = 0;
                PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->where('id', $lastBatch->id)->update(['stock' => $lastStock]);
            }
            else if($lastStock < $quantity && count($allBatches) > 1) {
                $quantity = $quantity - $lastStock;
                PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->where('id', $lastBatch->id)->update(['stock' => 0]);
                $lastBatch->delete();
            }
            else if($lastStock == $quantity && count($allBatches) == 1){
                $quantity = 0;
                PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->where('id', $lastBatch->id)->update(['stock' => 0]);
            }
            else if($lastStock == $quantity && count($allBatches) > 1){
                $quantity = 0;
                $lastBatch->delete();
            }
            else {
                $quantity = 0;
                $lastStock-=$quantity;
                if(count($allBatches) == 0) PartnerPosServiceBatch::create(['partner_pos_service_id' => $this->service->id, 'stock' => $lastStock, 'cost' => 0.0]);
                else PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->where('id', $lastBatch->id)->update(['stock' => $lastStock]);
            }
        }
        return $lastStock;
    }

    private function increaseUpdatedStock($quantity)
    {
        $lastBatch = PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->latest()->first();
        $lastStock = $lastBatch ? $lastBatch->stock : 0;
        PartnerPosServiceBatch::where('partner_pos_service_id', $this->service->id)->where('id', $lastBatch->id)->update(['stock' => $lastStock + $quantity]);
        return $lastStock + $quantity;
    }
}