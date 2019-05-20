<?php namespace Sheba\Business\Inspection;


use App\Models\Business;
use App\Models\Inspection;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Business\FormTemplateRepository;
use Sheba\Repositories\Business\InspectionItemRepository;
use Sheba\Repositories\Business\InspectionRepository;

class Creator
{
    private $inspectionRepository;
    private $inspectionItemRepository;
    private $formTemplateRepository;
    private $inspectionData;
    private $inspectionItemData;
    private $data;
    private $business;

    public function __construct(InspectionRepository $inspection_repository, InspectionItemRepository $inspection_item_repository, FormTemplateRepository $form_template_repository)
    {
        $this->inspectionRepository = $inspection_repository;
        $this->inspectionItemRepository = $inspection_item_repository;
        $this->formTemplateRepository = $form_template_repository;
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

    public function create()
    {
        $this->makeInspectionData();
        $inspection = null;
        try {
            /** @var Inspection $inspection */
            $inspection = $this->inspectionRepository->create($this->inspectionData);
            $this->makeInspectionItemData($inspection);
            $this->inspectionItemRepository->createMany($this->inspectionItemData);
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            throw  $e;
        }
        return $inspection;
    }

    private function makeInspectionData()
    {
        $this->inspectionData = [
            'title' => $this->data['title'],
            'short_description' => $this->data['short_description'],
            'long_description' => $this->data['long_description'],
            'vehicle_id' => $this->data['vehicle_id'],
            'member_id' => $this->data['member_id'],
            'business_id' => $this->business->id,
            'is_published' => 1
        ];
    }

    private function makeInspectionItemData(Inspection $inspection)
    {
        $form_template = $this->formTemplateRepository->find((int)$this->data['form_template_id']);
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

    private function calculateStartDate()
    {
        if ($this->data['schedule_type'] == 'monthly') {
            $date = date('Y') . '-' . date('m') . '-' . $this->data['schedule_type_value'] . ' ' . $this->data['schedule_time'];
            $this->data['start_date'] = Carbon::parse($date);
        } elseif ($this->data['schedule_type'] == 'one_way') {
            $date = $this->data['schedule_type_value'] . ' ' . $this->data['schedule_time'];
            $this->data['start_date'] = Carbon::parse($date);
        } elseif ($this->data['schedule_type'] == 'weekly') {
            $this->data['date_values'] = $this->data['schedule_type_value'];
        }
    }
}