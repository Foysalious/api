<?php namespace Sheba\Business\InspectionItem;

use App\Models\Inspection;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;

class Creator
{
    private $inspectionItemRepository;
    private $inspection;
    private $inspectionItemData;
    private $data;

    public function __construct(InspectionItemRepositoryInterface $inspection_item_repository)
    {
        $this->inspectionItemRepository = $inspection_item_repository;
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

    public function create()
    {
        $this->makeInspectionItemData();
        $this->inspectionItemRepository->createMany($this->inspectionItemData);
    }

    private function makeInspectionItemData()
    {
        $variables = json_decode($this->data['variables']);
        foreach ($variables as $variable) {
            array_push($this->inspectionItemData, [
                'title' => $variable->title,
                'short_description' => $variable->short_description,
                'long_description' => $variable->instructions,
                'input_type' => $variable->type,
                'inspection_id' => $this->inspection->id,
                'variables' => json_encode(['is_required' => (int)$variable->is_required]),
            ]);
        }
    }

}