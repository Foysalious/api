<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use App\Sheba\JobTime;
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
                ->publishedForAll()
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
}
