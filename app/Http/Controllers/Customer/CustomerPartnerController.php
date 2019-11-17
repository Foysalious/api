<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerPartnerController extends Controller
{

    public function getPreferredPartners($customer, Request $request)
    {
        try {
            $partners = [
                [
                    'id' => 233,
                    'name' => 'express',
                    'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1519727255_express_solution.jpg'
                ],
                [
                    'id' => 3,
                    'name' => 'ETC Service Solution',
                    'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1512992702_etc_service_solutions.jpg'
                ],
                [
                    'id' => 60121,
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