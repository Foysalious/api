<?php namespace Sheba\Partner;

use App\Jobs\DeductPartnerImpression;
use App\Models\Customer;
use App\Models\ImpressionDeduction;
use Carbon\Carbon;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Portals\Portals;

class ImpressionManager
{
    /** @var PartnerListRequest */
    private $request;

    public function setPartnerListRequest(PartnerListRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    public function deduct($partners)
    {
        $impression_deduction = new ImpressionDeduction();
        $impression_deduction->category_id = $this->request->selectedCategory->id;
        $impression_deduction->location_id = $this->request->location;
        $serviceArray = [];
        foreach ($this->request->selectedServices as $service) {
            array_push($serviceArray, [
                'id' => $service->id,
                'quantity' => $service->quantity,
                'option' => $service->option
            ]);
        }
        $impression_deduction->order_details = json_encode(['services' => $serviceArray]);
        $customer = request()->hasHeader('User-Id') && request()->header('User-Id') ? Customer::find((int)request()->header('User-Id')) : null;
        if ($customer) $impression_deduction->customer_id = $customer->id;
        $impression_deduction->portal_name = $this->request->portalName;
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
