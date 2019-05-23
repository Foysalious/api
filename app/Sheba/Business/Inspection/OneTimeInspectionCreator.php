<?php


namespace Sheba\Business\Inspection;

use DB;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionScheduleRepositoryInterface;

class OneTimeInspectionCreator extends Creator
{
    public function __construct(InspectionRepositoryInterface $inspection_repository, InspectionScheduleRepositoryInterface $inspection_schedule_repository, InspectionItemRepositoryInterface $inspection_item_repository, FormTemplateRepositoryInterface $form_template_repository)
    {
        parent::__construct($inspection_repository, $inspection_schedule_repository, $inspection_item_repository, $form_template_repository);
    }

    public function create()
    {
        $inspection = null;
        try {
            DB::transaction(function () use (&$inspection) {
                $this->makeInspectionData();
                $inspection = $this->inspectionRepository->create($this->inspectionData);
                $this->makeInspectionItemData([$inspection]);
                $this->inspectionItemRepository->createMany($this->inspectionItemData);
            });
        } catch (QueryException $e) {
            throw  $e;
        }
        return $inspection;
    }

    protected function makeInspectionData()
    {
        array_push($this->inspectionData, [
            'member_id' => $this->data['member_id'],
            'vehicle_id' => $this->data['vehicle_id'],
            'business_id' => $this->business->id,
            'is_published' => 1,
            'form_template_id' => $this->data['form_template_id'],
            'start_date' => $this->data['start_date'],
            'type' => $this->data['schedule_type'],
        ]);
    }
}