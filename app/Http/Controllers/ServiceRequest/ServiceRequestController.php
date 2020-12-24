<?php namespace App\Http\Controllers\ServiceRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Customer\Creator as CustomerCreator;
use Sheba\InfoCall\Creator as InfoCallCreator;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class ServiceRequestController extends Controller
{
    public function store(Request $request, ProfileRepositoryInterface $profile_repository, CustomerCreator $customer_creator, InfoCallCreator $creator)
    {
        $request->merge(['mobile' => formatMobile($request->mobile)]);
        $this->validate($request, [
            'service_name' => 'required|string',
            'location_id' => 'required|numeric',
            'name' => 'required|string',
            'mobile' => 'required|string|mobile:bd'
        ]);
        $profile = $profile_repository->findByMobile($request->mobile)->first();
        $customer = $profile ? $profile->customer : null;

        if (!$customer)
            $customer = $customer_creator->setMobile($request->mobile)->setName($request->name)->create();

        $creator->setServiceName($request->service_name)
            ->setName($request->name)
            ->setMobile($request->mobile)
            ->setCustomer($customer)
            ->setLocationId($request->location_id)
            ->create();

        return api_response($request, $customer, 200);
    }
}
