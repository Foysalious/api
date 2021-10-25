<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use App\Models\CategoryRequest;
use App\Models\Partner;
use App\Models\PartnerGeoChangeLog;
use App\Models\PartnerResource;
use App\Models\PartnerWorkingHour;
use App\Repositories\PartnerRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;
use Sheba\Dal\PartnerLocation\PartnerLocationRepository;
use Sheba\ModificationFields;
use Sheba\Partner\PartnerStatuses;
use Sheba\Partner\StatusChanger;
use Sheba\RequestIdentification;
use Throwable;

class OperationController extends Controller
{
    use ModificationFields;

    /** @var PartnerLocationRepository */
    private $partnerLocationRepo;
    private $category_request_data;

    public function __construct(PartnerLocationRepository $location_repo)
    {
        $this->partnerLocationRepo = $location_repo;
    }

    public function index($partner, Request $request)
    {
        $partner = $request->partner->load(['locations' => function ($q) {
            $q->select('id', 'name', 'partner_id');
        }, 'categories' => function ($q) {
            $q->select('categories.id', 'categories.name', 'partner_id');
        }, 'basicInformations']);
        $working_hours = $partner->workingHours()->select('id', 'partner_id', 'day', 'start_time', 'end_time')->get();
        $final = collect($partner)->only(['id', 'name']);
        $final->put('address', $partner->address);
        $final->put('working_schedule', $working_hours);
        $final->put('locations', $partner->locations->each(function ($location) {
            removeRelationsAndFields($location);
        }));
        $final->put('categories', $partner->categories->each(function ($category) {
            removeRelationsAndFields($category);
        }));
        return api_response($request, $final, 200, ['partner' => $final]);
    }

    public function store($partner, Request $request)
    {
        $this->validate($request, ['address' => "sometimes|required|string", 'locations' => "sometimes|required", 'working_schedule' => "sometimes|required", 'is_home_delivery_available' => "sometimes|required", 'is_on_premise_available' => "sometimes|required", 'delivery_charge' => "sometimes|required",]);
        if (($request->has('is_home_delivery_available') && !$request->is_home_delivery_available) && ($request->has('is_on_premise_available') && !$request->is_on_premise_available)) {
            return api_response($request, null, 400, ['message' => "You have to select at least one delivery option"]);
        }

        $partner = $request->partner;
        $this->setModifier($partner);
        $this->saveInDatabase($partner, $request);
        return api_response($request, $partner, 200);
    }

    private function saveInDatabase($partner, Request $request)
    {
        DB::transaction(function () use ($request, $partner) {
            $partner_info = [];
            if ($request->has('locations')) $partner->locations()->sync(json_decode($request->locations));
            if ($request->has('address')) $partner_info['address'] = $request->address;
            if ($request->has('lat') && $request->has('lng')) {
                $old_geo_informations = $partner->geo_informations;
                $partner_info['geo_informations'] = json_encode([
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'radius' => $partner->geo_informations ? (json_decode($partner->geo_informations)->radius ?: 1) : 1
                    //'radius' => $request->has('radius') ? ($request->radius / 1000) : (json_decode($partner->geo_informations)->radius ?: 1)
                ]);

                $geo_change_log_data = ['old_geo_informations' => $old_geo_informations, 'new_geo_informations' => $partner_info['geo_informations'], 'log' => 'Partner Geo Information Updated'];

                $partner->geoChangeLogs()->save(new PartnerGeoChangeLog($this->withCreateModificationField((new RequestIdentification())->set($geo_change_log_data))));

                $this->partnerLocationRepo->updateByPartnerId($partner->id, ['location' => array('type' => 'Point', 'coordinates' => [(double)$request->lng, (double)$request->lat]), 'radius' => (double)$request->radius,]);
            }

            $partner->update($partner_info);

            if ($request->has('working_schedule')) {
                $partner->workingHours()->delete();
                foreach (json_decode($request->working_schedule) as $working_schedule) {
                    $partner->workingHours()->save(new PartnerWorkingHour(['day' => $working_schedule->day, 'start_time' => $working_schedule->start_time, 'end_time' => $working_schedule->end_time]));
                }
            }

            $category_partner_info = [];
            $should_update_category_partner = 0;

            if ($request->has('is_home_delivery_available') && $request->has('delivery_charge')) {
                $category_partner_info['is_home_delivery_applied'] = $request->is_home_delivery_available;
                $category_partner_info['delivery_charge'] = $request->is_home_delivery_available ? $request->delivery_charge : 0;
                $should_update_category_partner = 1;
            }
            if ($request->has('is_on_premise_available')) {
                $category_partner_info['is_partner_premise_applied'] = $request->is_on_premise_available;
                $should_update_category_partner = 1;
            }

            if ($should_update_category_partner) {
                CategoryPartner::where('partner_id', $partner->id)->update($category_partner_info);
            }

            if (isPartnerReadyToVerified($partner)) {
                $status_changer = new StatusChanger($partner, ['status' => PartnerStatuses::WAITING]);
                $status_changer->change();
            }
        });
    }

    /**
     * @param $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCategories($partner, Request $request)
    {
        $this->validate($request, ['categories' => "required|string", 'category_name' => 'string']);
        $manager_resource = $request->manager_resource;
        $partner = $request->partner;
        $category_name = $request->category_name;

        $by = ["created_by" => $manager_resource->id, "created_by_name" => "Resource - " . $manager_resource->profile->name];
        $categories = array_unique(json_decode($request->categories));
        $categories = Category::whereIn('id', $categories)->get();
        $categories->load('services');
        list($services, $category_partners) = $this->makeCategoryPartnerWithServices($partner, $categories, $by);

        DB::transaction(function () use ($partner, $category_partners, $services, $category_name) {
            if (!empty($category_partners)) {
                $partner->categories()->sync($category_partners);
                $partner_resources = PartnerResource::whereIn('id', $partner->handymanResources->pluck('pivot.id')->toArray())->get();
                $category_ids = $partner->categories->pluck('id')->toArray();
                $partner_resources->each(function ($partner_resource) use ($category_ids) {
                    $partner_resource->categories()->sync($category_ids);
                });
            }

            if (!empty($services)) $partner->services()->sync($services);

            if (isPartnerReadyToVerified($partner)) {
                $status_changer = new StatusChanger($partner, ['status' => PartnerStatuses::WAITING]);
                $status_changer->change();
            }

            if ($category_name) {
                $this->category_request_data[count($this->category_request_data)] = [
                    'partner_id' => $partner->id,
                    'category_name' => $category_name
                ];
                array_walk($this->category_request_data, function (&$item) {
                    return $item['created_at'] = Carbon::now();
                });
                CategoryRequest::insert($this->category_request_data);
            }
        });

        return api_response($request, $partner, 200);
    }

    /**
     * @param Partner $partner
     * @param $categories
     * @param $by
     * @return array
     */
    private function makeCategoryPartnerWithServices(Partner $partner, $categories, $by)
    {
        $services = [];
        $category_partners = [];
        $location = (new PartnerRepository($partner))->getLocations()->pluck('id');
        $this->category_request_data = [];
        try {
            foreach ($categories as $category) {
                if ($category->isParent()) {
                    $published_secondary_category = $category->children()->published()->get();
                    if ($published_secondary_category->isEmpty()) {
                        $this->category_request_data[] = ['partner_id' => $partner->id, 'category_name' => $category->name];
                        return [$services, $category_partners];
                    }

                    foreach ($published_secondary_category as $secondary_category) {
                        $current_category_partner = $this->makeCategoryPartner($partner, $secondary_category, $location);
                        array_push($category_partners, array_merge($current_category_partner, $by));

                        foreach ($secondary_category->services as $service) {
                            $service_data = $this->makePartnerService($service, $secondary_category);
                            array_push($services, array_merge($by, $service_data));
                        }
                    }
                } else {
                    $current_category_partner = $this->makeCategoryPartner($partner, $category, $location);
                    array_push($category_partners, array_merge($current_category_partner, $by));

                    foreach ($category->services as $service) {
                        $service_data = $this->makePartnerService($service, $category);
                        array_push($services, array_merge($by, $service_data));
                    }
                }
            }
        } catch (Throwable $exception) {
            logError($exception);
        }

        return [$services, $category_partners];
    }

    public function isOnPremiseAvailable($partner, Request $request)
    {
        $partner = $request->partner;
        $is_on_premise_applicable = $partner->categories()->where('categories.is_partner_premise_applied', 1)->count() ? 1 : 0;
        return api_response($request, $is_on_premise_applicable, 200, ['is_on_premise_applicable' => $is_on_premise_applicable]);
    }

    /**
     * @param Partner $partner
     * @param $secondary_category
     * @param $location
     * @return array
     */
    private function makeCategoryPartner(Partner $partner, $secondary_category, $location)
    {
        $current_category_partner = [
            'response_time_min' => constants('PARTNER_MINIMUM_RESPONSE_TIME'),
            'response_time_max' => constants('PARTNER_MAXIMUM_RESPONSE_TIME'),
            'commission' => $partner->commission,
            'preparation_time_minutes' => 120,
            'category_id' => $secondary_category->id
        ];

        if ($partner->package_id === (int)config('sheba.partner_lite_packages_id')) $current_category_partner['is_partner_premise_applied'] = true;

        $secondary_category->load(['services' => function ($q) use ($location) {
            $q->whereExists(function ($query) use ($location) {
                $query->from('location_service')->whereIn('location_id', $location)->whereRaw('service_id=services.id');
            })->publishedForAll();
        }]);

        return $current_category_partner;
    }

    /**
     * @param $service
     * @param $secondary_category
     * @return array
     */
    private function makePartnerService($service, $secondary_category)
    {
        if ($service->variable_type == 'Fixed') {
            $options = null;
            $price = json_decode($service->variables)->price;
        } else {
            $options = '';
            foreach (json_decode($service->variables)->options as $key => $option) {
                $input = explode(',', $option->answers);
                $output = implode(',', array_map(function ($value, $key) {
                    return sprintf("%s", $key);
                }, $input, array_keys($input)));
                $output = '[' . $output . '],';
                $options .= $output;
            }
            $options = '[' . substr($options, 0, -1) . ']';
            $price = ($service->variable_type == 'Options') ? json_encode(json_decode($service->variables)->prices) : "Custom";
        }

        $service_data = [
            'description' => $service->description,
            'options' => $options,
            'prices' => $price,
            'is_published' => 1,
            'service_id' => $service->id
        ];

        if (in_array($secondary_category->id, config('sheba.car_rental.secondary_category_ids'))) {
            $service_variables = json_decode($service->variables);
            if ($service_variables->base_prices && $service_variables->base_quantity) {
                $service_data += [
                    'base_quantity' => json_encode($service_variables->base_quantity),
                    'base_prices' => json_encode($service_variables->base_prices)
                ];
            }
        }

        return $service_data;
    }
}
