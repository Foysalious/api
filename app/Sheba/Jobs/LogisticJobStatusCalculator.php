<?php namespace Sheba\Jobs;

use App\Models\Job;
use Carbon\Carbon;
use Sheba\Logistics\Repository\OrderRepository;

class LogisticJobStatusCalculator
{
    private $job;
    private $jobStatuses = [
        'Delivery_Scheduled' => 'Delivery Scheduled',
        'Rider_Searching' => 'Rider Searching',
        'Rider_Not_Found' => 'Rider Not Found',
        'Rider_Assigned' => 'Rider Assigned',
        'On_The_Way' => 'On The Way',
        'Delivered' => 'Delivered',
        'Delivery_Cancelled' => 'Cancelled'
    ];
    private $status;
    private $logisticOrderRepo;
    private $logisticOrder;

    public function __construct(Job $job)
    {
        $this->job = $job;
        $this->logisticOrderRepo = app( OrderRepository::class);
    }

    private function getLogisticOrderDetails()
    {
        $order_id = $this->job->last_logistic_order_id ?: $this->job->first_logistic_order_id;
        if($order_id) $this->logisticOrder = $this->logisticOrderRepo->find($order_id);
    }

    public function hasLogisticOrder()
    {
        return $this->job->last_logistic_order_id || $this->job->last_logistic_order_id;
    }

    /**
     * @throws \Exception
     */
    private function resolveStatus()
    {
        switch ($this->logisticOrder['status']) {
            case 'pending':
                $order_time = Carbon::parse($this->job->schedule_date." ".$this->job->preferred_time_start);
                $current_time = Carbon::now();
                $time_remaining_till_order_starts = $current_time->diffInMinutes($order_time);
                if($time_remaining_till_order_starts > 60) {
                    $this->status =  $this->jobStatuses['Delivery_Scheduled'];
                } else if ($time_remaining_till_order_starts > 0 && $time_remaining_till_order_starts <=60){
                    $this->status =  $this->jobStatuses['Rider_Searching'];
                } else {
                    $this->status =  $this->jobStatuses['Rider_Not_Found'];
                }
                break;
            case 'search_started':
                $this->status =  $this->jobStatuses['Rider_Searching'];
                break;
            case 'rider_not_found':
                $this->status = $this->jobStatuses['Rider_Not_Found'];
                break;
            case 'assigned':
                $this->status =  $this->jobStatuses['Rider_Assigned'];
                break;
            case 'picked':
                $this->status =  $this->jobStatuses['On_The_Way'];
                break;
            case 'dropped':
                $this->status =  $this->jobStatuses['Delivered'];
                break;
            case 'cancelled':
                $this->status =  $this->jobStatuses['Delivery_Cancelled'];
                break;
            default:
                throw new \Exception('Invalid Status Exception');
                break;

        }
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function calculate()
    {
        $this->getLogisticOrderDetails();
        $this->resolveStatus();
        return $this;
    }

    public function get()
    {
        return [
            'status' => $this->status,
            'data' => [
                'rider' => $this->logisticOrder['rider'] ?: null,
                'order_id' => $this->logisticOrder['id'] ?: null
            ]
        ];
    }

}