<?php namespace Sheba\Business\Inspection;


use App\Models\Business;
use App\Models\Inspection;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use DB;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;

class Creator
{
    private $inspectionRepository;
    private $inspectionItemRepository;
    private $formTemplateRepository;
    private $inspectionData;
    private $inspectionItemData;
    private $data;
    private $business;

    public function __construct(InspectionRepositoryInterface $inspection_repository, InspectionItemRepositoryInterface $inspection_item_repository, FormTemplateRepositoryInterface $form_template_repository)
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
            DB::transaction(function () use (&$inspection) {
                /** @var Inspection $inspection */
                $inspection = $this->inspectionRepository->create($this->inspectionData);
                $this->makeInspectionItemData($inspection);
                $this->inspectionItemRepository->createMany($this->inspectionItemData);
            });
        } catch (QueryException $e) {
            throw  $e;
        }
        return $inspection;
    }

    private function makeInspectionData()
    {
        $this->calculateDateValues();
        $this->inspectionData = [
            'vehicle_id' => $this->data['vehicle_id'],
            'member_id' => $this->data['member_id'],
            'business_id' => $this->business->id,
            'is_published' => 1,
            'form_template_id' => $this->data['form_template_id'],
            'start_date' => $this->data['start_date'],
            'next_start_date' => $this->data['next_start_date'],
            'date_values' => $this->data['date_values'],
            'type' => $this->data['schedule_type'],
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

    private function calculateDateValues()
    {
        $this->data['date_values'] = $this->data['next_start_date'] = null;
        if ($this->data['schedule_type'] == 'monthly') {
            $date = date('Y') . '-' . date('m') . '-' . $this->data['schedule_type_value'] . ' ' . $this->data['schedule_time'];
            $date = Carbon::parse($date);
            $this->data['start_date'] = Carbon::now() > $date ? $date->addDays(30) : $date;
            $this->data['next_start_date'] = $date->copy()->addDays(30);
        } elseif ($this->data['schedule_type'] == 'one_time') {
            $date = $this->data['schedule_type_value'] . ' ' . $this->data['schedule_time'];
            $this->data['start_date'] = Carbon::parse($date);
        } elseif ($this->data['schedule_type'] == 'weekly') {
            $current_day = date('l');
            if ($current_day == $this->data['schedule_type_value']) {
                $this->data['start_date'] = Carbon::now();
                $this->data['next_start_date'] = Carbon::parse('next ' . $this->data['schedule_type_value']);
            } else {
                $day = Carbon::parse('next ' . $this->data['schedule_type_value']);
                $this->data['start_date'] = $day;
                $this->data['next_start_date'] = $day->copy()->addDays(7);
            }
        }
    }
}