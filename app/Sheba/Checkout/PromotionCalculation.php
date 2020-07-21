<?php namespace Sheba\Checkout;

use App\Models\Location;
use App\Sheba\Checkout\Discount;
use App\Sheba\Checkout\PartnerList;
use Carbon\Carbon;
use Sheba\Checkout\Requests\PartnerListRequest;

class PromotionCalculation
{
    public function calculateOrderAmount(PartnerListRequest $request, $partner)
    {
        $partner_list = new PartnerList();
        $request->setAvailabilityCheck(1);
        $partner_list->setPartnerListRequest($request);
        $partner_list->find($partner);
        if ($partner_list->hasPartners) {
            $partner = $partner_list->partners->first();
            $order_amount = 0;
            $delivery_charge = (new DeliveryCharge())
                ->setCategory($request->selectedCategory)
                ->setLocation($request->getLocation())
                ->setCategoryPartnerPivot($partner->categories->first()->pivot)
                ->get();

            //(double)$category_pivot->delivery_charge;

            foreach ($request->selectedServices as $selected_service) {
                $service = $partner->services->where('id', $selected_service->id)->first();
                $schedule_date_time = Carbon::parse(request()->get('date') . ' ' . explode('-', request()->get('time'))[0]);
                $discount = new Discount();
                $discount->setServiceObj($selected_service)->setServicePivot($service->pivot)->setScheduleDateTime($schedule_date_time)->initialize();
                if ($discount->__get('hasDiscount')) return null;
                $order_amount += $discount->__get('discounted_price');
            }

            return $order_amount + $delivery_charge;
        } else {
            return null;
        }
    }
}
