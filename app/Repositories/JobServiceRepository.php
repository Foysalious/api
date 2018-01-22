<?php

namespace App\Repositories;


use App\Models\JobService;
use App\Models\PartnerService;
use App\Models\PartnerServiceDiscount;
use App\Sheba\Checkout\Discount;
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

    public function save(PartnerService $partner_service, Array $data)
    {
        try {
            $service = $partner_service->service;
            $data['name'] = $service->name;
            $data['variable_type'] = $service->variable_type;
            list($data['option'], $data['variables']) = $this->partnerServiceRepository->getVariableOptionOfService($service, $partner_service, $data['option']);
            if ($partner_service->service->isOptions()) {
                $data['unit_price'] = $this->partnerServiceRepository->getPriceOfOptionsService($partner_service->prices, $data['option']);
            } else {
                $data['unit_price'] = (double)$partner_service->prices;
            }
            $discount = new Discount($data['unit_price'], $data['quantity']);
            $discount->calculateServiceDiscount((PartnerServiceDiscount::find($partner_service->id)));
            $data['sheba_contribution'] = $discount->__get('sheba_contribution');
            $data['partner_contribution'] = $discount->__get('partner_contribution');
            $data['discount_id'] = $discount->__get('discount_id');
            $data['discount'] = $discount->__get('discount');
            $data['discount_percentage'] = $discount->__get('discount_percentage');
            return JobService::create($data);
        } catch (QueryException $e) {
            return false;
        }
    }


}