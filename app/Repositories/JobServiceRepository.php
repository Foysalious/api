<?php

namespace App\Repositories;


use App\Models\JobService;
use Illuminate\Database\QueryException;

class JobServiceRepository
{
    private $discountRepository;
    private $partnerServiceRepository;

    public function __construct()
    {
        $this->discountRepository = new DiscountRepository();
        $this->partnerServiceRepository = new PartnerServiceRepository();
    }

    public function save($partner_service, Array $data, $calculate_discount = true)
    {
        try {
            $service = $partner_service->service;
            $data['name'] = $service->name;
            $data['variable_type'] = $service->variable_type;
            list($data['unit_price'], $data['option'], $data['variables']) = $this->partnerServiceRepository->getVariableOptionPriceOfService($service, $partner_service, $data['option']);
            if ($running_discount = $partner_service->discount()) {
                $data['sheba_contribution'] = $running_discount->sheba_contribution;
                $data['partner_contribution'] = $running_discount->partner_contribution;
                $data['discount_id'] = $running_discount->id;
                $data['discount'] = $this->discountRepository->getServiceDiscountAmount($running_discount, (double)$data['unit_price'], (double)$data['quantity']);
                $data['discount_percentage'] = $running_discount->is_amount_percentage ? $running_discount->amount : 0;
            }
            return JobService::create($data);
        } catch (QueryException $e) {
            return false;
        }
    }


}