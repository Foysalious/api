<?php namespace Sheba\Business\Inspection;


use App\Models\InspectionSchedule;
use Carbon\Carbon;
use DB;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionScheduleRepositoryInterface;
use Sheba\Subscription\Types\MonthlySubscriptionType;
use Sheba\Subscription\Types\WeeklySubscriptionType;

class ScheduleInspectionCreator extends Creator
{
    private $inspectionScheduleDate;
    private $uptoDate;

    public function __construct(InspectionRepositoryInterface $inspection_repository, InspectionScheduleRepositoryInterface $inspection_schedule_repository, InspectionItemRepositoryInterface $inspection_item_repository, FormTemplateRepositoryInterface $form_template_repository)
    {
        parent::__construct($inspection_repository, $inspection_schedule_repository, $inspection_item_repository, $form_template_repository);
        $this->inspectionScheduleDate = [];
        $this->uptoDate = Carbon::now()->addMonths(6);
    }

    public function create()
    {
        $this->makeInspectionScheduleData();
        $inspection = null;
        try {
            DB::transaction(function () use (&$inspection) {
                /** @var InspectionSchedule $inspection_schedule */
                $inspection_schedule = $this->inspectionScheduleRepository->create($this->inspectionScheduleData);
                $this->makeInspectionData();
                $this->mergeScheduleId($inspection_schedule);
                $this->inspectionRepository->createMany($this->inspectionData);
                $inspections = $this->inspectionRepository->where('inspection_schedule_id', $inspection_schedule->id)->select(['id'])->get();
                $this->makeInspectionItemData($inspections);
                $this->inspectionItemRepository->createMany($this->inspectionItemData);
                $inspection=$inspections->first();
            });
        } catch (QueryException $e) {
            throw  $e;
        }
        return $inspection;
    }

    private function calculateInspectionScheduleDates()
    {
        $type = $this->data['schedule_type'];
        $type_class = $type == 'monthly' ? new MonthlySubscriptionType() : new WeeklySubscriptionType();
        $upto_date = Carbon::parse($this->uptoDate->toDateString() . ' ' . $this->data['schedule_time']);
        $this->inspectionScheduleDate = $type_class->setValues(json_decode($this->data['schedule_type_value']))
            ->seTime($this->data['schedule_time'])->seToDate($upto_date)->getDates();
    }

    public function makeInspectionScheduleData()
    {
        $this->calculateInspectionScheduleDates();
        $this->inspectionScheduleData = [
            'is_published' => 1,
            'date_values' => $this->data['schedule_type_value'],
            'type' => $this->data['schedule_type'],
        ];
    }

    protected function makeInspectionData()
    {
        foreach ($this->inspectionScheduleDate as $date) {
            array_push($this->inspectionData, [
                'member_id' => $this->data['inspector_id'],
                'vehicle_id' => $this->data['vehicle_id'],
                'business_id' => $this->business->id,
                'is_published' => 1,
                'form_template_id' => $this->data['form_template_id'],
                'start_date' => $date->toDateTimeString(),
                'type' => $this->data['schedule_type'],
            ]);
        }
    }

    private function mergeScheduleId($inspection_schedule)
    {
        foreach ($this->inspectionData as &$data) {
            $data['inspection_schedule_id'] = $inspection_schedule->id;
        }
    }

}