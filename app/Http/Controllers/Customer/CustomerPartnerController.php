<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Location\Geo;
use Sheba\PartnerList\Recommended;
use Sheba\ServiceRequest\ServiceRequest;

class CustomerPartnerController extends Controller
{

    public function getPreferredPartners($customer, Request $request, Recommended $recommended, Geo $geo, ServiceRequest $service_request)
    {
        try {
            $this->validate($request, [
                'services' => 'required|string',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);
            $service_request_object = $service_request->setServices(json_decode($request->services, 1))->get();
            $geo->setLat($request->lat)->setLng($request->lng);
            $partners = $recommended->setCustomer($request->customer)->setGeo($geo)->setServiceRequestObject($service_request_object)->get();
            if (!$partners) return api_response($request, null, 404);
            return api_response($request, $partners, 200, ['partners' => $partners->map(function (&$partner) {
                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'rating' => round((double)$partner->reviews[0]->avg_rating, 2),
                    'last_order_created_at' => '3/11/19',
                    'logo' => $partner->logo
                ];
            })]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}