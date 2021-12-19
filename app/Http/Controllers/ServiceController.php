<?php namespace App\Http\Controllers;

use Sheba\Dal\Category\Category;
use App\Models\HyperLocal;
use App\Models\Location;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use App\Transformers\ServiceV2Transformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\PriceCalculation;
use Sheba\Subscription\ApproximatePriceCalculator;
use Throwable;

class ServiceController extends Controller
{
    use Helpers;

    /** @var ServiceRepository $serviceRepository */
    private $serviceRepository;
    /** @var ReviewRepository $reviewRepository */
    private $reviewRepository;

    /**
     * ServiceController constructor.
     *
     * @param ServiceRepository $srp
     * @param ReviewRepository $reviewRepository
     */
    public function __construct(ServiceRepository $srp, ReviewRepository $reviewRepository)
    {
        $this->serviceRepository = $srp;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $services = Service::select('id', 'name', 'bn_name', 'unit', 'category_id', 'thumb', 'slug', 'min_quantity', 'banner', 'variable_type');

            if($request->orderby_id && in_array($request->orderby_id, ['asc', 'desc'])){
                $services = $services->orderBy('id', $request->orderby_id);
            }

            $scope = ['start_price'];
            if ($request->filled('is_business')) $services = $services->publishedForBusiness();
            if ($request->filled('is_b2b')) $services->publishedForB2B();
            if ($request->filled('is_ddn')) $services->publishedForDdn();
            $services = $services->skip($offset)->take($limit)->get();
            $services = $this->serviceRepository->getpartnerServicePartnerDiscount($services);
            $services = $this->serviceRepository->addServiceInfo($services, $scope);
            if ($request->filled('is_business') || $request->filled('is_ddn')) {
                $categories = $services->unique('category_id')->pluck('category_id')->toArray();
                $master_categories = Category::select('id', 'parent_id')->whereIn('id', $categories)->get()
                    ->pluck('parent_id', 'id')->toArray();
                $services->map(function ($service) use ($master_categories) {
                    $service['master_category_id'] = $master_categories[$service->category_id];
                });
            }
            return count($services) != 0 ? api_response($request, $services, 200, ['services' => $services]) : api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getLpg(Request $request, ApproximatePriceCalculator $approximatePriceCalculator,
                           PriceCalculation $price_calculation, DeliveryCharge $delivery_charge,
                           JobDiscountHandler $job_discount_handler)
    {

        $lpg_service_id = config('sheba.lpg_service_id');
        return $this->get($lpg_service_id, $request, $approximatePriceCalculator, $price_calculation, $delivery_charge,
            $job_discount_handler);
    }

    public function get($service, Request $request, ApproximatePriceCalculator $approximatePriceCalculator,
                        PriceCalculation $price_calculation, DeliveryCharge $delivery_charge,
                        JobDiscountHandler $job_discount_handler)
    {
        ini_set('memory_limit', '2048M');
        $service = Service::where('id', (int)$service)->select('id', 'name', 'unit', 'structured_description', 'stock', 'stock_left', 'category_id', 'short_description', 'description', 'thumb', 'slug', 'min_quantity', 'banner', 'faqs', 'bn_name', 'bn_faqs', 'variable_type', 'variables');
        $service_groups = $service->first() ? $service->first()->groups : null;
        $offers = collect();
        if ($service_groups) {
            $service_groups->map(function ($service_group) use ($offers) {
                $offer = $service_group->offers()->active()->flash()->validFlashOffer()->orderBy('end_date', 'desc')->first();
                if ($offer) $offers->push($offer);
            });
            $offer = $offers->sortBy('end_date')->first();
        }
        $options = $this->serviceQuestionSet($service->first());
        $answers = collect();
        if ($options)
            foreach ($options as $option) {
                $answers->push($option["answers"]);
            }

        $price_range = $approximatePriceCalculator->setService($service->first())->getMinMaxPartnerPrice();
        $service_max_price = $price_range[0] > 0 ? $price_range[0] : 0;
        $service_min_price = $price_range[1] > 0 ? $price_range[1] : 0;

        $service_breakdown = [];

        $location = null;
        if ($request->filled('lat') && $request->filled('lng')) {
            $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if (!is_null($hyperLocation)) $location = $hyperLocation->location_id;
        }
        $location = !is_null($location) ? $location : 4;
        $location_service = LocationService::where('location_id', $location)->where('service_id', $service->first()->id)->first();
        if (!$location_service) return api_response($request, null, 404, ['message' => 'Service is not available at this location.']);
        $price_calculation->setLocationService($location_service);

        if ($options) {
            if (count($answers) > 1) {
                $service_breakdown = $this->breakdown_service_with_min_max_price($answers, $service_min_price, $service_max_price, 0, $price_calculation, $location_service);
            } else {
                $total_breakdown = [];
                foreach ($answers[0] as $index => $answer) {
                    $breakdown = [
                        'name' => $answer,
                        'indexes' => [$index],
                        'min_price' => $service_min_price,
                        'max_price' => $service_max_price,
                        'price' => $price_calculation->setOption([$index])->getUnitPrice()
                    ];
                    array_push($total_breakdown, $breakdown);
                }
                $service_breakdown = $total_breakdown;
            }

        } else {
            $service_breakdown = [
                [
                    'name' => $service->first()->name,
                    'indexes' => null,
                    'min_price' => $service_min_price,
                    'max_price' => $service_max_price,
                    'price' => $price_calculation->getUnitPrice()
                ]
            ];
        }

        $service = $request->filled('is_business') ? $service->publishedForBusiness() : ($request->filled('is_ddn') ? $service->publishedForDdn() : $service->publishedForAll());
        $service = $service->first();

        if ($service == null) return api_response($request, null, 404);
        if ($service->variable_type == 'Options') {
            $service['first_option'] = $this->serviceRepository->getFirstOption($service);
        }

        $scope = [];
        if ($request->filled('scope')) {
            $scope = $this->serviceRepository->getServiceScope($request->scope);
        }
        if (in_array('discount', $scope) || in_array('start_price', $scope)) {
            $service = $this->serviceRepository->getpartnerServicePartnerDiscount($service);
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
        $service['faqs'] = json_decode($service->faqs);

        $service['bn_faqs'] = $service->bn_faqs ? json_decode($service->bn_faqs) : null;
        $category = Category::with(['parent' => function ($query) {
            $query->select('id', 'name');
        }])->where('id', $service->category_id)->select('id', 'name', 'parent_id', 'video_link', 'slug', 'is_auto_sp_enabled')->first();

        array_add($service, 'category_name', $category->name);
        array_add($service, 'video_link', $category->video_link);
        array_add($service, 'category_slug', $category->slug);
        array_add($service, 'is_auto_sp_enabled', $category->is_auto_sp_enabled);
        array_add($service, 'master_category_id', $category->parent->id);
        array_add($service, 'master_category_name', $category->parent->name);
        array_add($service, 'service_breakdown', $service_breakdown);
        array_add($service, 'options', $options);

        /** @var ServiceDiscount $discount */
        $discount = $location_service->discounts()->running()->first();
        $service_discount = $discount ? [
            'value' => (double)$discount->amount,
            'is_percentage' => $discount->isPercentage(),
            'cap' => (double)$discount->cap
        ] : null;
        array_add($service, 'discount', $service_discount);

        $category_delivery_charge = $delivery_charge->setCategory($service->category)
            ->setLocation(Location::find($location))->get();
        array_add($service, 'delivery_charge', $category_delivery_charge);

        $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($category_delivery_charge);
        $job_discount_handler->setType(DiscountTypes::DELIVERY)->setCategory($service->category)->setCheckingParams($discount_checking_params)->calculate();
        /** @var Discount $delivery_discount */
        $delivery_discount = $job_discount_handler->getDiscount();
        $category_delivery_discount = $delivery_discount ? [
            'value' => (double)$delivery_discount->amount,
            'is_percentage' => $delivery_discount->is_percentage,
            'cap' => (double)$delivery_discount->cap,
            'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
        ] : null;
        array_add($service, 'delivery_discount', $category_delivery_discount);

        removeRelationsAndFields($service);
        if (config('sheba.online_payment_discount_percentage') > 0) {
            $discount_percentage = config('sheba.online_payment_discount_percentage');
            $payment_discount_percentage = "Save $discount_percentage% more by paying online after checkout!";
            array_add($service, 'payment_discount_percentage', $payment_discount_percentage);
        }
        if ($offer) {
            array_add($service, 'is_flash', $offer->is_flash);
            array_add($service, 'start_time', $offer->start_date->toDateTimeString());
            array_add($service, 'end_time', $offer->end_date->toDateTimeString());
        } else {
            array_add($service, 'is_flash', 0);
            array_add($service, 'start_time', null);
            array_add($service, 'end_time', null);
        }


        if ($request->filled('is_business') || $request->filled('is_ddn')) {
            $questions = null;
            $service['type'] = 'normal';
            if ($service->variable_type == 'Options') {
                $questions = $service->variables->options;
                foreach ($questions as &$question) {
                    $question = collect($question);
                    $question->put('input_type', $this->resolveInputTypeField($question->get('answers')));
                    $question->put('screen', count($questions) > 3 ? 'slide' : 'normal');
                    $explode_answers = explode(',', $question->get('answers'));
                    $question->put('answers', $explode_answers);
                }
                if (count($questions) == 1) {
                    $questions[0]->put('input_type', 'selectbox');
                }
            }
            array_add($service, 'questions', $questions);
            array_add($service, 'faqs', $service->faqs);
        }

        return api_response($request, $service, 200, ['service' => $service]);
    }

    /**
     * @param $service
     * @param Request $request
     * @param PriceCalculation $price_calculation
     * @param DeliveryCharge $delivery_charge
     * @param JobDiscountHandler $job_discount_handler
     * @return JsonResponse
     */
    public function show($service, Request $request, PriceCalculation $price_calculation, DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler)
    {
        if ($request->filled('lat') && $request->filled('lng')) {
            $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->first();
            if (!is_null($hyperLocation)) $location = $hyperLocation->location_id;
            else return api_response($request, null, 404);
        } else {
            $location = $request->filled('location') ? $request->location : 4;
        }
        $service = Service::find($service);
        if (!$service) return api_response($request, null, 404, ['message' => "We couldn't find service."]);
        $location_service = LocationService::where('location_id', $location)->where('service_id', $service->id)->first();
        if (!$location_service) return api_response($request, null, 404, ['message' => 'Service is not available at this location . ']);
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $resource = new Item($service, new ServiceV2Transformer($location_service, $price_calculation, $delivery_charge, $job_discount_handler));
        $service = $manager->createData($resource)->toArray();

        return api_response($request, null, 200, ['service' => $service]);
    }

    private function serviceQuestionSet($service)
    {
        $questions = null;
        if ($service->variable_type == 'Options') {
            $questions = json_decode($service->variables)->options;
            foreach ($questions as &$question) {
                $question = collect($question);
                $question->put('input_type', $this->resolveInputTypeField($question->get('answers')));
                $question->put('screen', count($questions) > 3 ? 'slide' : 'normal');
                $explode_answers = explode(',', $question->get('answers'));
                $question->put('answers', $explode_answers);
            }
            if (count($questions) == 1) {
                $questions[0]->put('input_type', 'selectbox');
            }
        }
        return $questions;
    }

    /**
     * @param $arrays
     * @param $min_price
     * @param $max_price
     * @param int $i
     * @param PriceCalculation $price_calculation
     * @param LocationService $location_service
     * @return array
     */
    private function breakdown_service_with_min_max_price($arrays, $min_price, $max_price, $i = 0, PriceCalculation $price_calculation, LocationService $location_service)
    {
        if (!isset($arrays[$i])) return [];
        if ($i == count($arrays) - 1) return $arrays[$i];

        $tmp = $this->breakdown_service_with_min_max_price($arrays, $min_price, $max_price, $i + 1, $price_calculation, $location_service);

        $result = [];

        foreach ($arrays[$i] as $array_index => $v) {
            foreach ($tmp as $index => $t) {

                $result[] = is_array($t) ?
                    [
                        'name' => $v . " - " . $t['name'],
                        'indexes' => array_merge([$array_index], $t['indexes']),
                        'min_price' => $t['min_price'],
                        'max_price' => $t['max_price'],
                        'price' => $price_calculation->setLocationService($location_service)->setOption(array_merge([$array_index], $t['indexes']))->getUnitPrice()
                    ] : [
                        'name' => $v . " - " . $t,
                        'indexes' => array($array_index, $index),
                        'min_price' => $min_price,
                        'max_price' => $max_price,
                        'price' => $price_calculation->setLocationService($location_service)->setOption([$array_index, $index])->getUnitPrice()
                    ];
            }
        }

        return $result;
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

    private function resolveInputTypeField($answers)
    {
        $answers = explode(',', $answers);
        return count($answers) <= 4 ? "radiobox" : "dropdown";
    }
}
