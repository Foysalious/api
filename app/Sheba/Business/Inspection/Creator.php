<?php namespace Sheba\Business\Inspection;


use App\Models\Business;
use App\Models\InspectionSchedule;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use DB;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionScheduleRepositoryInterface;
use Sheba\Subscription\Types\MonthlySubscriptionType;
use Sheba\Subscription\Types\WeeklySubscriptionType;

class Creator
{
    private $inspectionRepository;
    private $inspectionScheduleRepository;
    private $inspectionItemRepository;
    private $formTemplateRepository;
    private $inspectionData;
    private $inspectionScheduleData;
    private $inspectionItemData;
    private $inspectionScheduleDate;
    private $data;
    private $business;

    public function __construct(InspectionRepositoryInterface $inspection_repository, InspectionScheduleRepositoryInterface $inspection_schedule_repository, InspectionItemRepositoryInterface $inspection_item_repository, FormTemplateRepositoryInterface $form_template_repository)
    {
        $this->inspectionRepository = $inspection_repository;
        $this->inspectionItemRepository = $inspection_item_repository;
        $this->formTemplateRepository = $form_template_repository;
        $this->inspectionScheduleRepository = $inspection_schedule_repository;
        $this->inspectionData = [];
        $this->inspectionItemData = [];
        $this->inspectionScheduleDate = [];
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
        $this->makeInspectionScheduleData();
        $inspection = null;
        try {
            DB::transaction(function () use (&$inspection) {
                /** @var InspectionSchedule $inspection_schedule */
                $inspection_schedule = $this->inspectionScheduleRepository->create($this->inspectionScheduleData);
                $this->makeInspectionData($inspection_schedule);
                $this->inspectionRepository->createMany($this->inspectionData);
                $inspections= $this->inspectionRepository->where('inspection_schedule_id', $inspection_schedule->id)->select(['id'])->get();
                $this->makeInspectionItemData($inspections);
                $this->inspectionItemRepository->createMany($this->inspectionItemData);
            });
        } catch (QueryException $e) {
            throw  $e;
        }
        return $inspection;
    }

    public function makeInspectionScheduleData()
    {
        $this->inspectionScheduleData = [
            'is_published' => 1,
            'date_values' => $this->data['schedule_type_value'],
            'type' => $this->data['schedule_type'],
        ];
    }

    private function makeInspectionData(InspectionSchedule $inspection_schedule)
    {
        $this->calculateInspectionScheduleDates();
        if ($this->data['schedule_type'] === 'one_way') {
            array_push($this->inspectionData, [
                'member_id' => $this->data['member_id'],
                'vehicle_id' => $this->data['vehicle_id'],
                'business_id' => $this->business->id,
                'is_published' => 1,
                'form_template_id' => $this->data['form_template_id'],
                'start_date' => $this->data['start_date'],
                'type' => $this->data['schedule_type'],
                'inspection_schedule_id' => $inspection_schedule->id,
            ]);
        } else {
            foreach ($this->inspectionScheduleDate as $date) {
                array_push($this->inspectionData, [
                    'member_id' => $this->data['member_id'],
                    'vehicle_id' => $this->data['vehicle_id'],
                    'business_id' => $this->business->id,
                    'is_published' => 1,
                    'form_template_id' => $this->data['form_template_id'],
                    'start_date' => $date->toDateTimeString(),
                    'type' => $this->data['schedule_type'],
                    'inspection_schedule_id' => $inspection_schedule->id
                ]);
            }
        }
    }

    private function calculateInspectionScheduleDates()
    {
        $type = $this->data['schedule_type'];
        $type_class = $type == 'monthly' ? new MonthlySubscriptionType() : new WeeklySubscriptionType();
        $this->inspectionScheduleDate = $type_class->setValues(json_decode($this->data['schedule_type_value']))->seToDate(Carbon::parse("2019-12-12"))->getDates();
    }

    private function makeInspectionItemData($inspections)
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