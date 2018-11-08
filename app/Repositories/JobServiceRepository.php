<?php

namespace App\Repositories;

use App\Models\Job;
use App\Models\JobService;
use App\Models\PartnerService;
use App\Models\Service;
use App\Sheba\Checkout\Discount;
use Carbon\Carbon;

class JobServiceRepository
{
    private $partnerServiceRepository;
    private $job;

    public function __construct()
    {
        $this->partnerServiceRepository = new PartnerServiceRepository();
    }

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function createJobService($services, $selected_services, $data)
    {
        $job_services = collect();
        foreach ($selected_services as $selected_service) {
            $service = $services->where('id', $selected_service->id)->first();
            $schedule_date_time = Carbon::parse($this->job->date . ' ' . $this->job->preferred_time_start);
            $discount = new Discount();
            $discount->setServiceObj($selected_service)->setServicePivot($service->pivot)->setScheduleDateTime($schedule_date_time)->initialize();
            $discount->calculateServiceDiscount();
            $service_data = array(
                'service_id' => $selected_service->id,
                'quantity' => $selected_service->quantity,
                'created_by' => $data['created_by'],
                'created_by_name' => $data['created_by_name'],
                'unit_price' => $discount->unit_price,
                'min_price' => $discount->min_price,
                'sheba_contribution' => $discount->__get('sheba_contribution'),
                'partner_contribution' => $discount->__get('partner_contribution'),
                'discount_id' => $discount->__get('discount_id'),
                'discount' => $discount->__get('discount'),
                'discount_percentage' => $discount->__get('discount_percentage'),
                'name' => $service->serviceModel->name,
                'variable_type' => $service->serviceModel->variable_type,
            );
            list($service_data['option'], $service_data['variables']) = $this->getVariableOptionOfService($service->serviceModel, $selected_service->option);
            $job_services->push(new JobService($service_data));
        }
        return $job_services;
    }

    private function getVariableOptionOfService(Service $service, Array $option)
    {
        if ($service->isOptions()) {
            $variables = [];
            foreach ((array)(json_decode($service->variables))->options as $key => $service_option) {
                array_push($variables, [
                    'title' => isset($service_option->title) ? $service_option->title : null,
                    'question' => $service_option->question,
                    'answer' => explode(',', $service_option->answers)[$option[$key]]
                ]);
            }
            $options = implode(',', $option);
            $option = '[' . $options . ']';
            $variables = json_encode($variables);
        } else {
            $option = '[]';
            $variables = '[]';
        }
        return array($option, $variables);
    }

    public function existInJob(Job $job, $job_services)
    {
        $services = $job->jobServices()->select('service_id', 'option')->get();
        foreach ($job_services as $job_service) {
            $service = $services->where('service_id', $job_service->service_id)->where('option', $job_service->option);
            if (count($service) > 0) {
                return true;
            }
        }
        return false;
    }
}