<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use DB;

class ServiceController extends Controller
{
    use Helpers;
    private $serviceRepository;
    private $reviewRepository;

    public function __construct(ServiceRepository $srp, ReviewRepository $reviewRepository)
    {
        $this->serviceRepository = $srp;
        $this->reviewRepository = $reviewRepository;
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('type')) {
                $type = strtoupper($request->type);
            } else {
                return api_response($request, null, 404);
            }
            $location = $request->has('location') ? $request->location : 4;
            $services = constants($type);
            $services = Service::whereIn('id', $services)
                ->select('id', 'name', 'unit', 'category_id', 'thumb', 'slug', 'min_quantity', 'banner', 'variable_type')
                ->published()
                ->get();
            $services = $this->serviceRepository->getpartnerServicePartnerDiscount($services, $location);
            $services = $this->serviceRepository->addServiceInfo($services, ['start_price']);
            return api_response($request, $services, 200, ['services' => $services]);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function get($service, Request $request)
    {
        try {
            $service = Service::where('id', $service)
                ->select('id', 'name', 'unit', 'category_id', 'description', 'thumb', 'slug', 'min_quantity', 'banner', 'faqs', 'variable_type', 'variables')
                ->published()
                ->first();
            if ($service == null)
                return api_response($request, null, 404);
            if ($service->variable_type == 'Options') {
                $service['first_option'] = $this->serviceRepository->getFirstOption($service);
            }
            $scope = [];
            if ($request->has('scope')) {
                $scope = $this->serviceRepository->getServiceScope($request->scope);
            }
            if (in_array('discount', $scope) || in_array('start_price', $scope)) {
                $service = $this->serviceRepository->getpartnerServicePartnerDiscount($service, $request->location);
            }
            if (in_array('reviews', $scope)) {
                $service->load('reviews');
            }
            $variables = json_decode($service->variables);
            unset($variables->max_prices);
            unset($variables->min_prices);
            unset($variables->prices);
            $services = [];
            array_push($services, $service);
            $service = $this->serviceRepository->addServiceInfo($services, $scope)[0];
            $service['variables'] = $variables;
            $category = Category::with(['parent' => function ($query) {
                $query->select('id', 'name');
            }])->where('id', $service->category_id)->select('id', 'name', 'parent_id')->first();
            array_add($service, 'category_name', $category->name);
            array_add($service, 'master_category_id', $category->parent->id);
            array_add($service, 'master_category_name', $category->parent->name);
            return api_response($request, $service, 200, ['service' => $service]);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function getPartners($service, $location = null, Request $request)
    {
        try {
            if ($request->getMethod() == 'GET') {
                $service = Service::where('id', $service)
                    ->select('id', 'name', 'unit', 'category_id', 'description', 'thumb', 'slug', 'min_quantity', 'banner', 'faqs', 'variable_type', 'variables')
                    ->first();
                if ($service == null)
                    return response()->json(['code' => 404, 'msg' => 'no service found']);
                array_add($service, 'discount', $service->hasDiscounts());
                //Add first options in service for render purpose
                if ($service->variable_type == 'Options') {
                    $variables = json_decode($service->variables);
                    $first_option = key($variables->prices);
                    $first_option = array_map('intval', explode(',', $first_option));
                    array_add($service, 'first_option', $first_option);
                }
                // review count of this service
                $review = $service->reviews()->where('review', '<>', '')->count('review');
                $rating = $service->reviews()->avg('rating');
                array_add($service, 'review_count', $review);
                $service['rating'] = empty($rating) ? 5 : floor($rating);
                //get the category & parent of the service
                $category = Category::with(['parent' => function ($query) {
                    $query->select('id', 'name');
                }])->where('id', $service->category_id)->select('id', 'name', 'parent_id')->first();
                array_add($service, 'category_name', $category->name);
                array_add($service, 'parent_id', $category->parent->id);
                array_add($service, 'parent_name', $category->parent->name);
            } elseif ($request->getMethod() == 'POST') {
                if (!$this->validateDateAndTime($request)) {
                    return api_response($request, null, 403, ['result' => 'Selected Date or Time is invalid!']);
                }
                $service = Service::where('id', $service)
                    ->select('id', 'name', 'unit', 'category_id', 'description', 'min_quantity', 'thumb', 'banner', 'faqs', 'slug', 'variable_type', 'variables')
                    ->first();
            }
            array_add($service, 'web_link', env('SHEBA_FRONT_END_URL') . '/service/' . $service->id . '/' . $service->slug);
            //get partners of the service
            $service_partners = $this->serviceRepository->partners($service, $location, $request);
            $sorted_service_partners = collect($service_partners)->sortBy('discounted_price')->values()->all();
            $sorted_service_partners = $this->serviceRepository->_sortPartnerListByAvailability($sorted_service_partners);

            $sorted_service_partners = collect($sorted_service_partners)->sortBy(function ($sorted_service_partner) {
                $sorted_service_partner['mobile'] = $sorted_service_partner->getContactNumber();
            })->values()->all();
//        $sorted_service_partners = collect($service_partners)->sortBy(function ($service_partner) {
//            return sprintf('%-12s%s', $service_partner->discounted_price, $service_partner->rating);
//        })->values()->all();
            $service->variables = json_decode($service->variables);
            array_forget($service, 'partnerServices');
            if (count($service_partners) != 0) {
                unset($service->variables->max_prices);
                unset($service->variables->min_prices);
                unset($service->variables->prices);
                if ($service->variable_type == 'Fixed') {
                    unset($service->variables->max_price);
                    unset($service->variables->min_price);
                    unset($service->variables->price);
                }
                if ($request->getMethod() == 'GET') {
                    return response()->json(['service' => $service, 'service_partners' => $sorted_service_partners, 'times' => config('constants.JOB_PREFERRED_TIMES'), 'msg' => 'successful', 'code' => 200]);
                } elseif ($request->getMethod() == 'POST') {
                    return response()->json(['service_partners' => $sorted_service_partners, 'msg' => 'successful', 'code' => 200]);
                }
            }
            if ($request->getMethod() == 'GET') {
                return response()->json(['service' => $service, 'service_partners' => $sorted_service_partners, 'times' => config('constants.JOB_PREFERRED_TIMES'), 'msg' => 'no partner found in selected location', 'code' => 404]);
            } elseif ($request->getMethod() == 'POST') {
                return response()->json(['service_partners' => $sorted_service_partners, 'msg' => 'no partner found in selected location', 'code' => 404]);
            }
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function getPartnersOfLocation($service, $location = null, Request $request)
    {
        try {
            $service = Service::where('id', $service)
                ->select('id', 'name', 'unit', 'category_id', 'description', 'min_quantity', 'thumb', 'banner', 'faqs', 'slug', 'variable_type', 'variables')
                ->first();
            array_add($service, 'web_link', env('SHEBA_FRONT_END_URL') . '/service/' . $service->id . '/' . $service->slug);
            $service_partners = $this->serviceRepository->partners($service, $location, $request);
            $sorted_service_partners = collect($service_partners)->sortBy('discounted_price')->values()->all();
            $sorted_service_partners = $this->serviceRepository->_sortPartnerListByAvailability($sorted_service_partners);
            $service->variables = json_decode($service->variables);
            array_forget($service, 'partnerServices');
            if (count($service_partners) != 0) {
                unset($service->variables->max_prices);
                unset($service->variables->min_prices);
                unset($service->variables->prices);
                if ($service->variable_type == 'Fixed') {
                    unset($service->variables->max_price);
                    unset($service->variables->min_price);
                    unset($service->variables->price);
                }
                return api_response($request, $sorted_service_partners, 200, ['service_partners' => $sorted_service_partners]);
            }
            return api_response($request, null, 404);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }


    /**
     * Change partner according to the selected options
     * @param $service
     * @param null $location
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePartner($service, $location = null, Request $request)
    {
        if (!$this->validateDateAndTime($request)) {
            return api_response($request, null, 403, ['result' => 'Selected Date or Time is invalid!']);
        }
        $service = Service::find($service);
        $option = null;
        //get the selected options
        if ($request->has('options')) {
            $option = implode(',', $request->input('options'));
        }
        //check if any partner provide service in the location
        $service_partners = $this->serviceRepository->partnerWithSelectedOption($service, $option, $location, $request);
        $sorted_service_partners = collect($service_partners)->sortBy('discounted_price')->values()->all();
        $sorted_service_partners = $this->serviceRepository->_sortPartnerListByAvailability($sorted_service_partners);

        $sorted_service_partners = collect($sorted_service_partners)->sortBy(function ($sorted_service_partner) {
            $sorted_service_partner['mobile'] = $sorted_service_partner->getContactNumber();
        })->values()->all();
//        $sorted_service_partners = collect($service_partners)->sortBy(function ($service_partner) {
//            return sprintf('%-12s%s', $service_partner->discounted_price, $service_partner->rating);
//        })->values()->all();
        if (!empty($sorted_service_partners)) {
            return response()->json(['service_partners' => $sorted_service_partners, 'msg' => 'successful', 'code' => 200]);
        } else
            return response()->json(['msg' => 'no partner found', 'code' => 404]);
    }

    public function changePartnerWithoutLocation(Service $service, Request $request)
    {
        //get the selected options
        $option = implode(',', $request->input('options'));
        //check if any partner provide service in the location
        $service_partners = $this->serviceRepository->partnerWithSelectedOption($service, $option, $location = null, $request);
        if (!empty($service_partners)) {
            return response()->json(['service_partners' => $service_partners, 'msg' => 'successful', 'code' => 200]);
        } else
            return response()->json(['msg' => 'no partner found', 'code' => 404]);
    }

    public function checkForValidity($service, Request $request)
    {
        $service = Service::where('id', $service)->published()->first();
        return $service != null ? api_response($request, true, 200) : api_response($request, false, 404);
    }

    public function getReviews($service)
    {
        $service = Service::with(['reviews' => function ($q) {
            $q->select('id', 'service_id', 'partner_id', 'customer_id', 'review_title', 'review', 'rating', DB::raw('DATE_FORMAT(updated_at, "%M %d, %Y at %h:%i:%s %p") as time'))
                ->with(['partner' => function ($q) {
                    $q->select('id', 'name', 'status', 'sub_domain');
                }])->with(['customer' => function ($q) {
                    $q->select('id', 'profile_id')->with(['profile' => function ($q) {
                        $q->select('id', 'name');
                    }]);
                }])->orderBy('updated_at', 'desc');
        }])->select('id')->where('id', $service)->first();
        if (count($service->reviews) > 0) {
            $service = $this->reviewRepository->getGeneralReviewInformation($service);
            $breakdown = $this->reviewRepository->getReviewBreakdown($service->reviews);
            $service = $this->reviewRepository->filterReviews($service);
            return response()->json(['msg' => 'ok', 'code' => 200, 'service' => $service, 'breakdown' => $breakdown]);
        }
        return response()->json(['msg' => 'not found', 'code' => 404]);
    }

    public function getPrices($service)
    {
        $service = Service::find($service);
        $prices = $this->serviceRepository->getMaxMinPrice($service);
        return response()->json(['max' => $prices[0], 'min' => $prices[1], 'code' => 200]);
    }

    public function getSimilarServices($service, Request $request)
    {
        $service = Service::find($service);
        $category = $service->category;
        list($offset, $limit) = calculatePagination($request);
        $location = $request->location != '' ? $request->location : 4;
        $scope = [];
        if ($request->has('scope')) {
            $scope = $this->serviceRepository->getServiceScope($request->scope);
        }
        $category->load(['services' => function ($q) use ($offset, $limit, $service) {
            $q->select('id', 'category_id', 'name', 'banner', 'variables', 'variable_type', 'min_quantity')->where('id', '<>', $service->id)->skip($offset)->limit($limit);
        }]);
        $services = $this->serviceRepository->addServiceInfo($this->serviceRepository->getPartnerServicesAndPartners($category->services, $location), $scope);
        if (count($services) > 3) {
            return response()->json(['services' => $services, 'msg' => 'successful', 'code' => 200]);
        } else {
            return response()->json(['msg' => 'not found', 'code' => 404]);
        }
    }

    private function validateDateAndTime(Request $request)
    {
        if ($request->has('day')) {
            if ($request->day < Carbon::now()->toDateString()) {
                return false;
            }
        }
        if ($request->has('time')) {
            if (!array_has($this->getSelectableTimes(), $request->time)) {
                return false;
            }
        }
        return true;
    }

    private function getSelectableTimes()
    {
        $today_slots = [];
        foreach (constants('JOB_PREFERRED_TIMES') as $time) {
            if ($time == "Anytime" || Carbon::now()->lte(Carbon::createFromTimestamp(strtotime(explode(' - ', $time)[1])))) {
                $today_slots[$time] = $time;
            }
        }
        return $today_slots;
    }


}
