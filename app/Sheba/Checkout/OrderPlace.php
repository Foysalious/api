<?php

namespace Sheba\Checkout;

use App\Sheba\Checkout\PartnerList;

class OrderPlace
{
    private $partnerList;

    public function setPartnerList(PartnerList $partnerList)
    {
        $this->partnerList = $partnerList;
    }

    private function createJobService()
    {
//        $job_services = collect();
//        $partner = $partnerList->partners->first();
//        foreach ($partner->breakdown as $selected_service) {
//            /** @var ServiceObject $selected_service */
//            $service = $services->where('id', $selected_service->id)->first();
//            $schedule_date_time = Carbon::parse($this->orderData['date'] . ' ' . explode('-', $this->orderData['time'])[0]);
//            $discount = new Discount();
//            $discount->setServiceObj($selected_service)->setServicePivot($service->pivot)->setScheduleDateTime($schedule_date_time)->initialize();
//            $service_data = array(
//                'service_id' => $selected_service->id,
//                'quantity' => $selected_service->quantity,
//                'created_by' => $data['created_by'],
//                'created_by_name' => $data['created_by_name'],
//                'unit_price' => $discount->unit_price,
//                'min_price' => $discount->min_price,
//                'sheba_contribution' => $discount->__get('sheba_contribution'),
//                'partner_contribution' => $discount->__get('partner_contribution'),
//                'discount_id' => $discount->__get('discount_id'),
//                'discount' => $discount->__get('discount'),
//                'discount_percentage' => $discount->__get('discount_percentage'),
//                'name' => $service->name,
//                'variable_type' => $service->variable_type,
//                'surcharge_percentage' => $discount->surchargePercentage
//            );
//
//            list($service_data['option'], $service_data['variables']) = $this->getVariableOptionOfService($service, $selected_service->option);
//            $job_services->push(new JobService($service_data));
//        }
//        return $job_services;
    }
}