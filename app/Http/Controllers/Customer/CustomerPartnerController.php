<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Checkout\Services\ServiceObject;
use Sheba\PartnerList\Recommended;

class CustomerPartnerController extends Controller
{

    public function getPreferredPartners($customer, Request $request, Recommended $recommended,ServiceObject $service_object)
    {
        try {
            $this->validate($request, [
                'services' => 'required|string',
                'lat' => 'numeric',
                'lng' => 'numeric',
            ]);
            $recommended->setCustomer($request->customer)->
            $partners = [
                [
                    'id' => 233,
                    'name' => 'express',
                    'rating' => 5,
                    'last_order_created_at' => '3/11/19',
                    'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1519727255_express_solution.jpg'
                ],
                [
                    'id' => 3,
                    'rating' => 5,
                    'last_order_created_at' => '3/11/19',
                    'name' => 'ETC Service Solution',
                    'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1512992702_etc_service_solutions.jpg'
                ],
                [
                    'id' => 60121,
                    'rating' => 5,
                    'last_order_created_at' => '3/11/19',
                    'name' => "Dora's Food & Catering",
                    'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1554979096_doras_food_catering.JPG'
                ],
            ];

            return api_response($request, $partners, 200, ['partners' => $partners]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}