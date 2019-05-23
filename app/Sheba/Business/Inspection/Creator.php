<?php namespace Sheba\Business\Inspection;

use App\Models\Business;
use DB;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionScheduleRepositoryInterface;

abstract class Creator
{
    protected $inspectionRepository;
    protected $inspectionScheduleRepository;
    protected $inspectionItemRepository;
    protected $formTemplateRepository;
    protected $inspectionData;
    protected $inspectionScheduleData;
    protected $inspectionItemData;
    protected $data;
    protected $business;

    public function __construct(InspectionRepositoryInterface $inspection_repository, InspectionScheduleRepositoryInterface $inspection_schedule_repository,
                                InspectionItemRepositoryInterface $inspection_item_repository, FormTemplateRepositoryInterface $form_template_repository)
    {
        $this->inspectionRepository = $inspection_repository;
        $this->inspectionItemRepository = $inspection_item_repository;
        $this->formTemplateRepository = $form_template_repository;
        $this->inspectionScheduleRepository = $inspection_schedule_repository;
        $this->inspectionData = [];
        $this->inspectionItemData = [];
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    abstract public function create();

    abstract protected function makeInspectionData();

    protected function makeInspectionItemData(array $inspections)
    {
        $form_template = $this->formTemplateRepository->find((int)$this->data['form_template_id']);
        foreach ($inspections as $inspection) {
            foreach ($form_template->items as $item) {
                array_push($this->inspectionItemData, [
                    'title' => $item->title,
                    'short_description' => $item->short_description,
                    'long_description' => $item->long_description,
                    'input_type' => $item->input_type,
                    'inspection_id' => $inspection->id,
                    'variables' => $item->variables,
                ]);
            }
        }
    }
}