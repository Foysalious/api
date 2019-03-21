<?php

namespace App\Http\Controllers;

use App\Exceptions\HyperLocationNotFoundException;
use App\Models\Category;
use App\Models\CategoryPartner;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\Partner;
use App\Sheba\Checkout\PartnerList;
use App\Sheba\Checkout\Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Sheba\Checkout\Requests\PartnerListRequest;

class PartnerLocationController extends Controller
{
    public function getPartners(Request $request, PartnerListRequest $partnerListRequest)
    {
        try {
            $this->validate($request, [
                'date' => 'sometimes|required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'sometimes|required|string',
                'services' => 'required|string',
                'isAvailable' => 'sometimes|required',
                'partner' => 'sometimes|required',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'skip_availability' => 'numeric',
                'filter' => 'string|in:sheba',
            ]);
            $validation = new Validation($request);
            if (!$validation->isValid()) return api_response($request, $validation->message, 400, ['message' => $validation->message]);
            $partner = $request->has('partner') ? $request->partner : null;
            $partnerListRequest->setRequest($request)->prepareObject();
            $partner_list = new PartnerList();
            $partner_list->setPartnerListRequest($partnerListRequest)->find($partner);
            if ($request->has('isAvailable')) {
                $partners = $partner_list->partners;
                $available_partners = $partners->filter(function ($partner) {
                    return $partner->is_available == 1;
                });
                $is_available = count($available_partners) != 0 ? 1 : 0;
                return api_response($request, $is_available, 200, ['is_available' => $is_available, 'available_partners' => count($available_partners)]);
            }
            if ($partner_list->hasPartners) {
                $partner_list->addPricing();
                $partner_list->addInfo();
                if ($request->has('filter') && $request->filter == 'sheba') {
                    $partner_list->sortByShebaPartnerPriority();
                } else {
                    $partner_list->sortByShebaSelectedCriteria();
                }
                $partners = $partner_list->removeKeysFromPartner();
                return api_response($request, $partners, 200, ['partners' => $partners->values()->all()]);
            }
            return api_response($request, null, 404, ['message' => 'No partner found.']);
        } catch (HyperLocationNotFoundException $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 400, ['message' => 'Your are out of service area.']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getNearbyPartners(Request $request)
    {
        try {
            $location = null;
            if ($request->has('location')) {
                $location = Location::find($request->location);
            } else if ($request->has('lat')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location;
            }

            if(!$location)
                return api_response($request, 'Invalid location', 400, ['message' => 'Invalid location']);

            if($request->has('category_id')) {
                // Get All Partners in specific Category && Location
                $category_id = Category::find($request->category_id);
                $partners = [
                    [
                        'id' => 462,
                        'current_impression' => 110,
                        'address' => 'Nodda Bazar, Beside Sonali Bank, Baridhara, Gulshan, Dhaka-1212.',
                        'name' => 'Yasin Traders',
                        'sub_domain' => 'yasin-traders',
                        'description' => 'All kinds of Home and Office Relocation.',
                        'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1546778793_yasin_traders.png',
                        'service_category' => 'Appliance Repair',
                        'distance' => 1.2,
                        'lat' => 23.12121212,
                        'lng' => 91.121213123,
                        'badge' => 'silver',
                        'order_limit' => NULL,
                        'discount' => 0,
                        'discounted_price' => 2500,
                        'original_price' => 2500,
                        'is_min_price_applied' => 0,
                        'delivery_charge' => 0,
                        'has_home_delivery' => 1,
                        'has_premise_available' => 0,
                        'total_jobs' => 186,
                        'ongoing_jobs' => 0,
                        'total_jobs_of_category' => 186,
                        'total_completed_orders' => 186,
                        'subscription_type' => 'PSP',
                        'total_working_days' => 7,
                        'rating' => 4.64,
                        'total_ratings' => 99,
                        'total_five_star_ratings' => 78,
                        'total_compliments' => 78,
                        'total_experts' => 5,
                    ],
                    [
                        'id' => 462,
                        'current_impression' => 110,
                        'address' => 'Nodda Bazar, Beside Sonali Bank, Baridhara, Gulshan, Dhaka-1212.',
                        'name' => 'Yasin Traders',
                        'sub_domain' => 'yasin-traders',
                        'description' => 'All kinds of Home and Office Relocation.',
                        'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1546778793_yasin_traders.png',
                        'service_category' => 'Appliance Repair',
                        'distance' => 1.2,
                        'lat' => 23.12121212,
                        'lng' => 91.121213123,
                        'badge' => 'silver',
                        'order_limit' => NULL,
                        'discount' => 0,
                        'discounted_price' => 2500,
                        'original_price' => 2500,
                        'is_min_price_applied' => 0,
                        'delivery_charge' => 0,
                        'has_home_delivery' => 1,
                        'has_premise_available' => 0,
                        'total_jobs' => 186,
                        'ongoing_jobs' => 0,
                        'total_jobs_of_category' => 186,
                        'total_completed_orders' => 186,
                        'subscription_type' => 'PSP',
                        'total_working_days' => 7,
                        'rating' => 4.64,
                        'total_ratings' => 99,
                        'total_five_star_ratings' => 78,
                        'total_compliments' => 78,
                        'total_experts' => 5,
                    ],
                    [
                        'id' => 462,
                        'current_impression' => 110,
                        'address' => 'Nodda Bazar, Beside Sonali Bank, Baridhara, Gulshan, Dhaka-1212.',
                        'name' => 'Yasin Traders',
                        'sub_domain' => 'yasin-traders',
                        'description' => 'All kinds of Home and Office Relocation.',
                        'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1546778793_yasin_traders.png',
                        'service_category' => 'Appliance Repair',
                        'distance' => 1.2,
                        'lat' => 23.12121212,
                        'lng' => 91.121213123,
                        'badge' => 'silver',
                        'order_limit' => NULL,
                        'discount' => 0,
                        'discounted_price' => 2500,
                        'original_price' => 2500,
                        'is_min_price_applied' => 0,
                        'delivery_charge' => 0,
                        'has_home_delivery' => 1,
                        'has_premise_available' => 0,
                        'total_jobs' => 186,
                        'ongoing_jobs' => 0,
                        'total_jobs_of_category' => 186,
                        'total_completed_orders' => 186,
                        'subscription_type' => 'PSP',
                        'total_working_days' => 7,
                        'rating' => 4.64,
                        'total_ratings' => 99,
                        'total_five_star_ratings' => 78,
                        'total_compliments' => 78,
                        'total_experts' => 5,
                    ]
                ];
                return api_response($request, null, 200, [ 'partners' => $partners]);
            } else {
                //Find all partners in given location
                $partners = [
                    [
                        'id' => 462,
                        'current_impression' => 110,
                        'address' => 'Nodda Bazar, Beside Sonali Bank, Baridhara, Gulshan, Dhaka-1212.',
                        'name' => 'Yasin Traders',
                        'sub_domain' => 'yasin-traders',
                        'description' => 'All kinds of Home and Office Relocation.',
                        'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1546778793_yasin_traders.png',
                        'service_category' => 'Appliance Repair',
                        'distance' => 1.2,
                        'lat' => 23.12121212,
                        'lng' => 91.121213123,
                        'badge' => 'silver',
                        'order_limit' => NULL,
                        'discount' => 0,
                        'discounted_price' => 2500,
                        'original_price' => 2500,
                        'is_min_price_applied' => 0,
                        'delivery_charge' => 0,
                        'has_home_delivery' => 1,
                        'has_premise_available' => 0,
                        'total_jobs' => 186,
                        'ongoing_jobs' => 0,
                        'total_jobs_of_category' => 186,
                        'total_completed_orders' => 186,
                        'subscription_type' => 'PSP',
                        'total_working_days' => 7,
                        'rating' => 4.64,
                        'total_ratings' => 99,
                        'total_five_star_ratings' => 78,
                        'total_compliments' => 78,
                        'total_experts' => 5,
                    ],
                    [
                        'id' => 462,
                        'current_impression' => 110,
                        'address' => 'Nodda Bazar, Beside Sonali Bank, Baridhara, Gulshan, Dhaka-1212.',
                        'name' => 'Yasin Traders',
                        'sub_domain' => 'yasin-traders',
                        'description' => 'All kinds of Home and Office Relocation.',
                        'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1546778793_yasin_traders.png',
                        'service_category' => 'Appliance Repair',
                        'distance' => 1.2,
                        'lat' => 23.12121212,
                        'lng' => 91.121213123,
                        'badge' => 'silver',
                        'order_limit' => NULL,
                        'discount' => 0,
                        'discounted_price' => 2500,
                        'original_price' => 2500,
                        'is_min_price_applied' => 0,
                        'delivery_charge' => 0,
                        'has_home_delivery' => 1,
                        'has_premise_available' => 0,
                        'total_jobs' => 186,
                        'ongoing_jobs' => 0,
                        'total_jobs_of_category' => 186,
                        'total_completed_orders' => 186,
                        'subscription_type' => 'PSP',
                        'total_working_days' => 7,
                        'rating' => 4.64,
                        'total_ratings' => 99,
                        'total_five_star_ratings' => 78,
                        'total_compliments' => 78,
                        'total_experts' => 5,
                    ],
                    [
                        'id' => 462,
                        'current_impression' => 110,
                        'address' => 'Nodda Bazar, Beside Sonali Bank, Baridhara, Gulshan, Dhaka-1212.',
                        'name' => 'Yasin Traders',
                        'sub_domain' => 'yasin-traders',
                        'description' => 'All kinds of Home and Office Relocation.',
                        'logo' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1546778793_yasin_traders.png',
                        'service_category' => 'Appliance Repair',
                        'distance' => 1.2,
                        'lat' => 23.12121212,
                        'lng' => 91.121213123,
                        'badge' => 'silver',
                        'order_limit' => NULL,
                        'discount' => 0,
                        'discounted_price' => 2500,
                        'original_price' => 2500,
                        'is_min_price_applied' => 0,
                        'delivery_charge' => 0,
                        'has_home_delivery' => 1,
                        'has_premise_available' => 0,
                        'total_jobs' => 186,
                        'ongoing_jobs' => 0,
                        'total_jobs_of_category' => 186,
                        'total_completed_orders' => 186,
                        'subscription_type' => 'PSP',
                        'total_working_days' => 7,
                        'rating' => 4.64,
                        'total_ratings' => 99,
                        'total_five_star_ratings' => 78,
                        'total_compliments' => 78,
                        'total_experts' => 5,
                    ]
                ];
                return api_response($request, null, 200, [ 'partners' => $partners]);
            }

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }
}