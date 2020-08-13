<?php namespace Sheba\Analysis\PartnerPerformance\Calculators;

use App\Models\Job;
use Sheba\Dal\JobCancelReason\JobCancelReason;
use Sheba\Dal\JobNoResponseLog\JobNoResponseLog;
use Sheba\Dal\JobScheduleDueLog\JobScheduleDueLog;
use Sheba\Analysis\PartnerPerformance\Data\InnerData;
use Sheba\Analysis\PartnerPerformance\Data\OuterData;
use Sheba\Analysis\PartnerPerformance\Data\PartnerPerformanceData;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;
use Sheba\Dal\Complain\Model as Complain;
use Sheba\Helpers\TimeFrame;

class Basic extends PartnerPerformance
{
    private $ordersCreated = [];
    private $ordersServed = [];
    private $jobServedIds = [];
    private $actualJobCancelledCount = [];
    private $cancelledJobsThatDoesNotAffectPerformanceCount = [];

    protected function get()
    {
        return (new PartnerPerformanceData())
            ->setCompleted($this->getDataOf('completed'))
            ->setCancelled($this->getOrderCancelledCountOn($this->timeFrame))
            ->setNoComplain($this->getDataOf('complain'))
            ->setTimelyAccepted($this->getDataOf('timely_accepted'))
            ->setTimelyProcessed($this->getDataOf('timely_processed'))
            ->setOrderReceived($this->getOrderCreatedCountOn($this->timeFrame));
    }

    private function getDataOf($of)
    {
        $func_name = "getDataOf" . pamelCase($of);

        $previous = $this->isCalculatingWeekly() ? $this->getPreviousDataByWeekly($of) : $this->getPreviousDataByMonthly($of);

        /** @var InnerData $data */
        $data = $this->$func_name($this->timeFrame);

        return (new OuterData())->setInnerData($data)->setPrevious($previous);
    }

    private function getDataOfCompleted(TimeFrame $time_frame)
    {
        $order_closed = $this->getOrderClosedCountOn($time_frame);
        $order_created = $this->getOrderCreatedCountOn($time_frame);

        return (new InnerData())->setValue($order_closed)->setDenominator($order_created);
    }

    private function getDataOfComplain(TimeFrame $time_frame)
    {
        $complain = Complain::against($this->partner)->notRejected()->createdAtBetween($time_frame)->count();
        $order_closed = $this->getOrderClosedCountOn($time_frame);
        $without_complain = $order_closed - $complain;
        return (new InnerData())->setValue($without_complain)->setDenominator($order_closed);
    }

    private function getDataOfTimelyAccepted(TimeFrame $time_frame)
    {
        $jobs_served = $this->getJobsServedOn($time_frame);
        $not_responded = JobNoResponseLog::whereIn('job_id', $jobs_served)->count();
        $order_closed = $this->getOrderClosedCountOn($time_frame);
        $timely_accepted = $order_closed - $not_responded;
        return (new InnerData())->setValue($timely_accepted)->setDenominator($order_closed);
    }

    private function getDataOfTimelyProcessed(TimeFrame $time_frame)
    {
        $jobs_served = $this->getJobsServedOn($time_frame);
        $schedule_due = JobScheduleDueLog::whereIn('job_id', $jobs_served)->count();
        $order_closed = $this->getOrderClosedCountOn($time_frame);
        $timely_processed = $order_closed - $schedule_due;
        return (new InnerData())->setValue($timely_processed)->setDenominator($order_closed);
    }

    private function getPreviousDataByWeekly($of)
    {
        $func_name = "getDataOf" . pamelCase($of);

        $calculating_week = $this->timeFrame->start->weekOfYear;
        $week_start_date = $this->timeFrame->start->copy()->subWeeks(self::CALCULATE_PREVIOUS_SLOT);
        $week_end_date = $this->timeFrame->end->copy()->subWeeks(self::CALCULATE_PREVIOUS_SLOT);
        $data = [];
        for ($week = $calculating_week - self::CALCULATE_PREVIOUS_SLOT; $week < $calculating_week; $week++) {
            $data[] = [
                'name' => 'Week ' . $week,
                'date_range' => [
                    'start' => $week_start_date->toDateString(),
                    'end' => $week_end_date->toDateString()
                ],
                'data' => $this->$func_name(new TimeFrame($week_start_date, $week_end_date))
            ];

            $week_start_date->addWeek();
            $week_end_date->addWeek();
        }
        return $data;
    }

    private function getPreviousDataByMonthly($of)
    {
        $func_name = "getDataOf" . pamelCase($of);

        $calculating_month = $this->timeFrame->start->month;
        $month_start_date = $this->timeFrame->start->copy()->subMonths(self::CALCULATE_PREVIOUS_SLOT);
        $month_end_date = $this->timeFrame->end->copy()->subMonths(self::CALCULATE_PREVIOUS_SLOT);
        $data = [];
        for ($month = $calculating_month - self::CALCULATE_PREVIOUS_SLOT; $month < $calculating_month; $month++) {
            $data[] = [
                'name' => $month_start_date->format('M'),
                'date_range' => [
                    'start' => $month_start_date->toDateString(),
                    'end' => $month_end_date->toDateString()
                ],
                'data' => $this->$func_name(new TimeFrame($month_start_date, $month_end_date))
            ];
            $month_start_date->addMonth();
            $month_end_date->addMonth();
        }
        return $data;
    }

    private function getOrderCreatedCountOn(TimeFrame $time_frame)
    {
        $key = $this->getKey($time_frame);
        if (array_key_exists($key, $this->ordersCreated)) return $this->ordersCreated[$key];
        $total_order = $this->partner->orders()->createdAtBetween($time_frame)->count();
        $this->ordersCreated[$key] = $total_order - $this->getCancelledJobsThatDoesNotAffectPerformance($time_frame);
        return $this->ordersCreated[$key];
    }

    private function getCancelledJobsThatAffectsPerformance(TimeFrame $time_frame)
    {
        $key = $this->getKey($time_frame);
        if (array_key_exists($key, $this->actualJobCancelledCount)) return $this->actualJobCancelledCount[$key];

        $cancel_reasons_that_affects_performance = JobCancelReason::affectsPartnerPerformance()->pluck('key');
        $partners_jobs_that_affects_performance = $this->partner->jobs()->whereBetween('jobs.created_at',$time_frame->getArray())
            ->with(['cancelLog' => function($q) use ($cancel_reasons_that_affects_performance) {
                $q->whereIn('cancel_reason',$cancel_reasons_that_affects_performance);
            }])->get()->pluck('cancelLog.job_id')->toArray();

        $this->actualJobCancelledCount[$key] = count(array_filter($partners_jobs_that_affects_performance));
        return $this->actualJobCancelledCount[$key];
    }

    private function getCancelledJobsThatDoesNotAffectPerformance(TimeFrame $time_frame)
    {
        $key = $this->getKey($time_frame);
        if (array_key_exists($key, $this->cancelledJobsThatDoesNotAffectPerformanceCount))
            return $this->cancelledJobsThatDoesNotAffectPerformanceCount[$key];

        $cancel_reasons_that_affects_performance = JobCancelReason::affectsPartnerPerformance()->pluck('key');
        $partners_jobs_that_affects_performance = $this->partner->jobs()->whereBetween('jobs.created_at',$time_frame->getArray())
            ->with(['cancelLog' => function($q) use ($cancel_reasons_that_affects_performance) {
                $q->whereNotIn('cancel_reason',$cancel_reasons_that_affects_performance);
            }])->get()->pluck('cancelLog.job_id')->toArray();

        $this->cancelledJobsThatDoesNotAffectPerformanceCount[$key] = count(array_filter($partners_jobs_that_affects_performance));
        return $this->cancelledJobsThatDoesNotAffectPerformanceCount[$key];
    }

    private function getOrderClosedCountOn(TimeFrame $time_frame)
    {
        $key = $this->getKey($time_frame);
        if (array_key_exists($key, $this->ordersServed)) return $this->ordersServed[$key];
        $data = $this->partner->orders()->closedAtBetween($time_frame)->count();
        $this->ordersServed[$key] = $data;
        return $data;
    }

    private function getOrderCancelledCountOn(TimeFrame $time_frame)
    {
        return $this->getCancelledJobsThatAffectsPerformance($time_frame);
    }

    private function getJobsServedOn(TimeFrame $time_frame)
    {
        $key = $this->getKey($time_frame);
        if (array_key_exists($key, $this->jobServedIds)) return $this->jobServedIds[$key];
        $data = Job::select('id')->whereIn('partner_order_id', function ($q) {
            return $q->select('id')->from('partner_orders')->where('partner_id', $this->partner->id);
        })->deliveredAtBetween($time_frame)->pluck('id')->toArray();
        $this->jobServedIds[$key] = $data;
        return $data;
    }

    private function getKey(TimeFrame $time_frame)
    {
        return $time_frame->start->toDateString() . "-" . $time_frame->end->toDateString();
    }
}
