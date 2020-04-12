<?php namespace App\Http\Controllers\Service;

use App\Models\Category;
use App\Models\HyperLocal;
use App\Models\LocationService;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Sheba\Dal\JobService\JobService;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\ServiceRequest\ServiceRequestObject;

class ServicePricingController extends Controller
{
    /** @var Category */
    private $category;
    /** @var Collection */
    private $services;
    protected $serviceRequest;
    /** @var ServiceRequestObject[] */
    private $serviceRequestObject;
    private $location;
    /** @var PriceCalculation */
    private $priceCalculation;
    /** @var UpsellCalculation */
    private $upsellCalculation;
    /** @var DiscountCalculation */
    private $discountCalculation;
    /** @var float */
    private $orderAmountWithoutDeliveryCharge;
    /** @var float */
    private $orderTotalDiscount;

    public function __construct(ServiceRequest $serviceRequest, PriceCalculation $priceCalculation, UpsellCalculation $upsell_calculation, DiscountCalculation $discountCalculation)
    {
        $this->serviceRequest = $serviceRequest;
        $this->priceCalculation = $priceCalculation;
        $this->discountCalculation = $discountCalculation;
        $this->upsellCalculation = $upsell_calculation;
    }

    public function getCalculatedPricing(Request $request)
    {
        $this->setLocation($request->lat, $request->lng);
        $this->setServices($request->services);
        $services_list = $this->createServiceList();
        $this->calculateOrderAmount($services_list);
        $this->calculateTotalDiscount($services_list);
        $price = [];
        $price['total_original_price'] = $this->orderAmountWithoutDeliveryCharge;
        $price['total_discounted_price'] = $this->orderAmountWithoutDeliveryCharge - $this->orderTotalDiscount;
        $price['total_discount'] = $this->orderTotalDiscount;
        $price['breakdown'] = [];
        foreach ($services_list as $key => $service) {
            $price['breakdown'][$key]['id'] = $service['service_id'];
            $price['breakdown'][$key]['quantity'] = $service['quantity'];
            $price['breakdown'][$key]['unit_price'] = $service['unit_price'];
            $price['breakdown'][$key]['discount'] = $service['discount'];
            $price['breakdown'][$key]['option'] = json_decode($service['option']);
            $price['breakdown'][$key]['variables'] = json_decode($service['variables']);
            $price['breakdown'][$key]['original_price'] =$service['original_price'];
            $price['breakdown'][$key]['discounted_price'] = $service['discounted_price'];
        }
        return api_response($request, $price, 200, ['service_pricing' => $price]);

    }

    public function setServices($services)
    {
        $this->serviceRequestObject = $this->serviceRequest->setServices(json_decode($services, 1))->get();
        $this->category = $this->getCategory();
        return $this;
    }

    /**
     * @return Category
     */
    private function getCategory()
    {
        return $this->serviceRequestObject[0]->getCategory();
    }

    private function createServiceList()
    {
        $services_list = collect();
        foreach ($this->serviceRequestObject as $selected_service) {
            $service = $selected_service->getService();
            $location_service = LocationService::where([['service_id', $service->id], ['location_id', $this->location->id]])->first();
            $this->priceCalculation->setService($service)->setLocationService($location_service)->setOption($selected_service->getOption())->setQuantity($selected_service->getQuantity());
            $upsell_unit_price = $this->upsellCalculation->setService($service)->setLocationService($location_service)->setOption($selected_service->getOption())
                ->setQuantity($selected_service->getQuantity())->getUpsellUnitPriceForSpecificQuantity();
            $unit_price = $upsell_unit_price ? $upsell_unit_price : $this->priceCalculation->getUnitPrice();
            $total_original_price = $this->category->isRentACar() ? $this->priceCalculation->getTotalOriginalPrice() : $unit_price * $selected_service->getQuantity();
            $this->discountCalculation->setLocationService($location_service)->setOriginalPrice($total_original_price)->setQuantity($selected_service->getQuantity())->calculate();
            $service_data = [
                'service_id' => $service->id,
                'quantity' => $selected_service->getQuantity(),
                'unit_price' => $unit_price,
                'discount' => $this->discountCalculation->getJobServiceDiscount(),
                'original_price' => $unit_price * $selected_service->getQuantity(),
                'discounted_price' => ($unit_price * $selected_service->getQuantity()) - $this->discountCalculation->getJobServiceDiscount()
            ];
            list($service_data['option'], $service_data['variables']) = $service->getVariableAndOption($selected_service->getOption());
            $services_list->push($service_data);
        }
        return $services_list;
    }

    private function setLocation($lat, $lng)
    {
        $hyper_local = HyperLocal::insidePolygon($lat, $lng)->with('location')->first();
        $this->location = $hyper_local->location;
    }

    /**
     * @param $services_list
     */
    private function calculateOrderAmount($services_list)
    {
        $this->orderAmountWithoutDeliveryCharge = $services_list->map(function ($service) {
            return $service['unit_price'] * $service['quantity'];
        })->sum();
    }

    /**
     * @param $services_list
     */
    private function calculateTotalDiscount($services_list)
    {
        $this->orderTotalDiscount = $services_list->map(function ($service) {
            return $service['discount'];
        })->sum();
    }
}
