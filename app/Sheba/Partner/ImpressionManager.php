<?php namespace Sheba\Partner;

use App\Jobs\DeductPartnerImpression;
use App\Models\Customer;
use App\Models\ImpressionDeduction;
use App\Models\Location;
use Carbon\Carbon;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\FraudDetection\Category;
use Sheba\Portals\Portals;
use Sheba\ServiceRequest\ServiceRequestObject;

class ImpressionManager
{
    /** @var PartnerListRequest */
    private $request;
    private $locationId;
    private $categoryId;
    private $customerId;
    /** @var ServiceRequestObject[] */
    private $serviceRequestObject;
    private $services;
    private $portalName;

    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function setLocationId($location_id)
    {
        $this->locationId = $location_id;
        return $this;
    }


    public function setCategoryId($category_id)
    {
        $this->categoryId = $category_id;
        return $this;
    }

    /**
     * @param ServiceRequestObject[] $serviceRequestObject
     * @return ImpressionManager
     */
    public function setServiceRequestObject($serviceRequestObject)
    {
        $this->serviceRequestObject = $serviceRequestObject;
        $serviceArray = [];
        foreach ($this->serviceRequestObject as $selected_service) {
            array_push($serviceArray, [
                'id' => $selected_service->getServiceId(),
                'quantity' => $selected_service->getQuantity(),
                'option' => $selected_service->getOption()
            ]);
        }
        $this->setServices($serviceArray);
        $this->setPortalName(request()->header('portal-name'));
        return $this;
    }

    public function setPartnerListRequest(PartnerListRequest $request)
    {
        $this->request = $request;
        $this->setCategoryId($this->request->selectedCategory->id);
        $this->setLocationId($this->request->location);
        $serviceArray = [];
        foreach ($this->request->selectedServices as $service) {
            array_push($serviceArray, [
                'id' => $service->id,
                'quantity' => $service->quantity,
                'option' => $service->option
            ]);
        }
        $this->setServices($serviceArray);
        $this->setPortalName($this->request->portalName);
        return $this;
    }

    public function setServices(array $services)
    {
        $this->services = $services;
        return $this;
    }

    public function setPortalName($portal_name)
    {
        $this->portalName = $portal_name;
        return $this;
    }

    public function deduct(array $partners)
    {
        $impression_deduction = new ImpressionDeduction();
        $impression_deduction->category_id = $this->categoryId;
        $impression_deduction->location_id = $this->locationId;
        $impression_deduction->order_details = json_encode(['services' => $this->services]);
        $impression_deduction->customer_id = $this->customerId ? $this->customerId : null;
        $impression_deduction->portal_name = $this->portalName;
        $impression_deduction->ip = request()->ip();
        $impression_deduction->user_agent = request()->header('User-Agent');
        $impression_deduction->created_at = Carbon::now();
        $impression_deduction->save();
        $impression_deduction->partners()->sync($partners);
        dispatch(new DeductPartnerImpression($partners));
    }

    public function needsToDeduct()
    {
        return request()->has('screen') &&
            request()->get('screen') == 'partner_list' &&
            in_array(request()->header('Portal-Name'), $this->targetPortals());
    }

    private function targetPortals()
    {
        return [
            Portals::CUSTOMER_WEB, Portals::CUSTOMER_APP, Portals::PARTNER_WEB, Portals::PARTNER_APP
        ];
    }
}
