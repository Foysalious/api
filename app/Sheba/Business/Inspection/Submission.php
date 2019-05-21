<?php namespace Sheba\Business\Inspection;


use App\Models\Inspection;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;
use DB;

class Submission
{
    private $data;
    /** @var Inspection */
    private $inspection;
    private $inspectionRepository;
    private $inspectionItemRepository;
    private $inspectionData;
    private $inspectionItemData;

    public function __construct(InspectionRepositoryInterface $inspection_repository, InspectionItemRepositoryInterface $inspection_item_repository)
    {
        $this->inspectionRepository = $inspection_repository;
        $this->inspectionItemRepository = $inspection_item_repository;
        $this->inspectionData = [];
        $this->inspectionItemData = [];
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setInspection(Inspection $inspection)
    {
        $this->inspection = $inspection;
        return $this;
    }

    public function submit()
    {
        $this->makeInspectionData();
        try {
            DB::transaction(function () use (&$inspection) {
                /** @var Inspection $inspection */
                $inspection = $this->inspectionRepository->update($this->inspection, $this->inspectionData);
                $this->updateInspectionItems();
            });
        } catch (QueryException $e) {
            throw  $e;
        }
        return $inspection;
    }

    private function makeInspectionData()
    {
        $this->inspectionData = [
            'submitted_date' => Carbon::now(),
            'submission_note' => $this->data['submission_note'],
            'status' => 'closed'
        ];
    }

    private function updateInspectionItems()
    {
        $items = collect(json_decode($this->data['items']));
        foreach ($items as $item) {
            $result = $this->inspection->items->where('id', $item->id)->first();
            $this->inspectionItemRepository->update($result, [
                'result' => $item->result,
                'comment' => $item->comment
            ]);
        }
    }
}