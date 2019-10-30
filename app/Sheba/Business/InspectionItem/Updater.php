<?php namespace Sheba\Business\InspectionItem;


use App\Models\InspectionItem;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemStatusLogRepositoryInterface;
use DB;

class Updater
{
    private $inspectionItemStatusLogRepository;
    private $inspectionItemRepository;
    private $inspectionItem;

    public function setInspectionItem(InspectionItem $inspection_item)
    {
        $this->inspectionItem = $inspection_item;
        return $this;
    }

    public function __construct(InspectionItemRepositoryInterface $inspection_item_repository, InspectionItemStatusLogRepositoryInterface $inspection_item_status_log_repository)
    {
        $this->inspectionItemStatusLogRepository = $inspection_item_status_log_repository;
        $this->inspectionItemRepository = $inspection_item_repository;
    }

    public function updateStatus($status, $additional_info = [])
    {
        try {
            DB::transaction(function () use ($status, $additional_info) {
                $previous_status = $this->inspectionItem->status;
                $this->inspectionItemRepository->update($this->inspectionItem, array_merge(['status' => $status], $additional_info));
                $this->inspectionItemStatusLogRepository->create(['inspection_item_id' => $this->inspectionItem->id, 'to_status' => $status, 'from_status' => $previous_status]);
            });
        } catch (QueryException $e) {
            throw  $e;
        }
        return $this->inspectionItem;
    }
}