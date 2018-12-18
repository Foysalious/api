<?php namespace App\Http\Controllers\Partner;

use App\Models\Partner;
use App\Models\PartnerService;
use App\Models\PartnerServicePricesUpdate;
use App\Models\Service;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;

class PartnerServiceController extends Controller
{
    use ModificationFields;

    public function index(Request $request)
    {
        $partner_services = PartnerService::with(['service.category.parent'])->where('partner_id', $request->partner->id)->published()->withCount(['pricesUpdates' => function ($query) {
            $query->status(constants('PARTNER_SERVICE_UPDATE_STATUS')['Pending']);
        }])->get();
        $master_categories = collect();
        foreach ($partner_services as $partner_service) {
            if (!$partner_service->service->publication_status && !$partner_service->service->is_published_for_backend) continue;

            $master_category = $partner_service->service->category->parent;
            $master_category_in_collection = $master_categories->where('id', $master_category->id)->first();
            if (!$master_category_in_collection) {
                $master_categories->push([
                    'id' => $master_category->id,
                    'name' => $master_category->name,
                    'sub_categories' => collect([[
                        'id' => $partner_service->service->category->id,
                        'name' => $partner_service->service->category->name,
                        'services' => collect([[
                            'id' => $partner_service->service_id,
                            'name' => $partner_service->service->name,
                            'has_update_request' => $partner_service->prices_updates_count,
                            'thumb' => $partner_service->service->app_thumb
                        ]])
                    ]])
                ]);
            } else {
                $sub_category_in_collection = $master_category_in_collection['sub_categories']->where('id', $partner_service->service->category->id)->first();
                if (!$sub_category_in_collection) {
                    $master_category_in_collection['sub_categories']->push([
                        'id' => $partner_service->service->category->id,
                        'name' => $partner_service->service->category->name,
                        'services' => collect([[
                            'id' => $partner_service->service_id,
                            'name' => $partner_service->service->name,
                            'has_update_request' => $partner_service->prices_updates_count,
                            'thumb' => $partner_service->service->app_thumb
                        ]])
                    ]);
                } else {
                    $sub_category_in_collection['services']->push([
                        'id' => $partner_service->service_id,
                        'name' => $partner_service->service->name,
                        'has_update_request' => $partner_service->prices_updates_count,
                        'thumb' => $partner_service->service->app_thumb
                    ]);
                }
            }
        }
        return api_response($request, $partner_services, 200, ['master_categories' => $master_categories]);
    }

    public function store(Request $request)
    {
        try {
            $partner = $request->partner;
            $service_ids = explode(',', $request->id);
            foreach ($service_ids as $service_id) {
               $response = $this->validateAndStoreIndividualService($service_id, $partner, $request);
               if(!is_null($response)) return $response;
            }
            return api_response($request, 1, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update($partner, $service, Request $request)
    {
        try {
            $this->validate($request, [
                'options' => 'sometimes|string',
                'prices' => 'required|string',
                'min_prices' => 'sometimes|string',
                'base_prices' => 'sometimes|string',
                'base_quantity' => 'sometimes|string',
            ]);
            $partner = $request->partner;
            /**@var Service $service * */
            $service = $partner->services()->where('services.id', $service)->first();
            if (!$service) return api_response($request, null, 403, ['message' => 'Service is not added yet.']);
            $this->setModifier($request->manager_resource);
            $data = [];
            $prices = [];
            if ($service->isOptions()) {
                foreach (json_decode($request->prices) as $price_option) {
                    $prices[implode(array_values($price_option->option), ',')] = $price_option->price;
                }
                $data['prices'] = json_encode($prices);
                $data['options'] = $request->options;
            } else {
                $data['prices'] = $request->prices;
            }
            $data['min_prices'] = $request->min_prices;
            $data['base_prices'] = $request->base_prices;
            $data['base_quantity'] = $request->base_quantity;
            if ($partner->status == 'Verified') $this->_updateRequest($data, $partner, $service);
            else $this->_update($data, $service);
            return api_response($request, 1, 200);
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

    private function _updateRequest($data, Partner $partner, Service $service)
    {
        $partner_service = $service->pivot;
        $update_data = [
            'partner_service_id' => $partner_service->id,
            'old_options' => $partner_service->options,
            'new_options' => isset($data['options']) ? $data['options'] : $partner_service->options,
            'old_prices' => $partner_service->prices,
            'new_prices' => $data['prices'],
            'status' => 'Pending'
        ];
        if ($service->is_min_price_applicable) {
            $update_data['old_min_prices'] = $partner_service->min_prices ?: $data['min_prices'];
            $update_data['new_min_prices'] = $data['min_prices'];
        }

        if ($service->is_base_price_applicable) {
            $update_data['old_base_prices'] = $partner_service->base_prices ?: $data['base_prices'];
            $update_data['new_base_prices'] = $data['base_prices'];

            $update_data['old_base_quantity'] = $partner_service->base_quantity ?: $data['base_quantity'];
            $update_data['new_base_quantity'] = $data['base_quantity'];
        }
        PartnerServicePricesUpdate::create($this->withCreateModificationField($update_data));
        notify()->department(9)->send($this->createNotificationData($partner, $service));
    }

    private function createNotificationData(Partner $partner, $service)
    {
        return [
            "title" => $partner->name . " updated a service price.",
            "link" => config('sheba.admin_url') . "partners/" . $partner->id . "/service/" . $service,
            "type" => notificationType('Warning'),
            "event_type" => 'App\\Models\\Partner',
            "event_id" => $partner->id
        ];
    }

    private function _update($data, Service $service)
    {
        $pivot_data = $service->pivot;
        $data = $this->withUpdateModificationField($data);
        $pivot_data->update($data);
    }

    public function validateAndStoreIndividualService($service_id, $partner, $request)
    {
        $service = Service::find((int)$service_id);
        if (!$service) return api_response($request, null, 404, ['message' => 'Service not found']);
        if ($partner->services()->find($service->id)) return api_response($request, null, 403, ['message' => 'Service already added.','service_id' => $service_id]);
        if (!$partner->categories()->find($service->category_id)) return api_response($request, null, 403, ['message' => 'Category not added.']);
        /**@var Service $service * */
        $variables = json_decode($service->variables);
        $data = [];
        $data['description'] = $service->description;
        $data['prices'] = $service->isOptions() ? json_encode($variables->prices) : $variables->price;
        $data['options'] = $service->isOptions() ? createOptionsFromOptionVariables($variables) : null;
        $data['min_prices'] = isset($variables->min_prices) ? json_encode($variables->min_prices) : null;
        $data['base_prices'] = isset($variables->base_prices) ? json_encode($variables->base_prices) : null;
        $data['base_quantity'] = isset($variables->base_quantity) ? json_encode($variables->base_quantity) : null;
        $data['is_published'] = 1;
        $this->setModifier($request->manager_resource);
        $pivot_data = $this->withBothModificationFields($data);
        $partner->services()->save($service, $pivot_data);
    }
}
