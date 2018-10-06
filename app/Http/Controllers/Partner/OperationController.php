<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\PartnerWorkingHour;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;
use Sheba\Partner\StatusChanger;

class OperationController extends Controller
{
    public function index($partner, Request $request)
    {
        try {
            $partner = $request->partner->load(['locations' => function ($q) {
                $q->select('id', 'name', 'partner_id');
            }, 'categories' => function ($q) {
                $q->select('categories.id', 'categories.name', 'partner_id');
            }, 'basicInformations']);
            $working_hours = $partner->workingHours()->select('id', 'partner_id', 'day', 'start_time', 'end_time')->get();
            $final = collect($partner)->only(['id', 'name']);
            $final->put('address', $partner->basicInformations->address);
            $final->put('working_schedule', $working_hours);
            $final->put('locations', $partner->locations->each(function ($location) {
                removeRelationsAndFields($location);
            }));
            $final->put('categories', $partner->categories->each(function ($category) {
                removeRelationsAndFields($category);
            }));
            return api_response($request, $final, 200, ['partner' => $final]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'address'           => "sometimes|required|string",
                'locations'         => "sometimes|required",
                'working_schedule'  => "sometimes|required",
            ]);
            $partner = $request->partner;
            return $this->saveInDatabase($partner, $request) ? api_response($request, $partner, 200) : api_response($request, $partner, 500);
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

    private function saveInDatabase($partner, Request $request)
    {
        try {
            DB::transaction(function () use ($request, $partner) {
                $partner_info = [];
                if ($request->has('locations')) $partner->locations()->sync(json_decode($request->locations));
                if ($request->has('address')) $partner_info['address'] = $request->address;
                if ($request->has('lat') && $request->has('lng')) {
                    $partner_info['geo_informations'] = json_encode(['lat' => $request->lat, 'lng' => $request->lng, 'radius' => "10"]);
                }
                $partner->update($partner_info);

                if ($request->has('working_schedule')) {
                    $partner->workingHours()->delete();
                    foreach (json_decode($request->working_schedule) as $working_schedule) {
                        $partner->workingHours()->save(new PartnerWorkingHour([
                            'day'        => $working_schedule->day,
                            'start_time' => $working_schedule->start_time,
                            'end_time'   => $working_schedule->end_time
                        ]));
                    }
                }

                if (isPartnerReadyToVerified($partner)) {
                    $status_changer = new StatusChanger($partner, ['status' => constants('PARTNER_STATUSES')['Waiting']]);
                    $status_changer->change();
                }
            });

            return true;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }

    public function saveCategories($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'categories' => "required|string"
            ]);
            $manager_resource = $request->manager_resource;
            $by = ["created_by" => $manager_resource->id, "created_by_name" => "Resource - " . $manager_resource->profile->name];
            $categories = json_decode($request->categories);
            $categories = Category::whereIn('id', $categories)->get();
            $categories->load('services');
            $partner = $request->partner;
            list($services, $category_partners) = $this->makeCategoryPartnerWithServices($partner, $categories, $by);
            DB::transaction(function () use ($partner, $category_partners, $services) {
                $partner->categories()->sync($category_partners);
                $partner_resources = PartnerResource::whereIn('id', $partner->handymanResources->pluck('pivot.id')->toArray())->get();
                $category_ids = $partner->categories->pluck('id')->toArray();
                $partner_resources->each(function ($partner_resource) use ($category_ids) {
                    $partner_resource->categories()->sync($category_ids);
                });
                $partner->services()->sync($services);

                if (isPartnerReadyToVerified($partner)) {
                    $status_changer = new StatusChanger($partner, ['status' => constants('PARTNER_STATUSES')['Waiting']]);
                    $status_changer->change();
                }
            });
            return api_response($request, $partner, 200);
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

    private function makeCategoryPartnerWithServices(Partner $partner, $categories, $by)
    {
        $services = [];
        $category_partners = [];
        foreach ($categories as $category) {
            array_push($category_partners, array_merge(['response_time_min' => 60, 'response_time_max' => 120,
                'commission' => $partner->commission, 'category_id' => $category->id], $by));
            $category->load(['services' => function ($q) {
                $q->publishedForAll();
            }]);
            foreach ($category->services as $service) {
                if ($service->variable_type == 'Fixed') {
                    $options = null;
                    $price = json_decode($service->variables)->price;
                } else {
                    $options = '';
                    foreach (json_decode($service->variables)->options as $key => $option) {
                        $input = explode(',', $option->answers);
                        $output = implode(',', array_map(
                            function ($value, $key) {
                                return sprintf("%s", $key);
                            }, $input, array_keys($input)
                        ));
                        $output = '[' . $output . '],';
                        $options .= $output;
                    }
                    $options = '[' . substr($options, 0, -1) . ']';
                    $price = ($service->variable_type == 'Options') ? json_encode(json_decode($service->variables)->prices) : "Custom";
                }
                array_push($services, array_merge($by, [
                    'description' => $service->description,
                    'options' => $options,
                    'prices' => $price,
                    'is_published' => 1,
                    'service_id' => $service->id
                ]));
            }
        }
        return array($services, $category_partners);
    }
}