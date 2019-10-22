<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerPosService;
use App\Models\PartnerPosServiceDiscount;
use App\Models\PartnerPosServiceLog;
use App\Models\PosCategory;
use App\Transformers\PosServiceTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\ModificationFields;
use Sheba\Pos\Product\Creator as ProductCreator;
use Sheba\Pos\Product\Deleter;
use Sheba\Pos\Product\Log\FieldType;
use Sheba\Pos\Product\Updater as ProductUpdater;
use Sheba\Pos\Repositories\PosServiceDiscountRepository;
use Sheba\Reward\ActionRewardDispatcher;
use Throwable;
use Tinify\Exception;

class ServiceController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $partner = $request->partner;
            $services = [];
            $base_query = PartnerPosService::with('discounts')->published();

            if ($request->has('category_id') && !empty($request->category_id)) {
                $category_ids = explode(',', $request->category_id);
                $base_query->whereIn('pos_category_id', $category_ids);
            }

            $base_query->select($this->getSelectColumnsOfService())
                ->partner($partner->id)->get()
                ->each(function ($service) use (&$services) {
                    $services[] = [
                        'id' => $service->id,
                        'name' => $service->name,
                        'app_thumb' => $service->app_thumb,
                        'app_banner' => $service->app_banner,
                        'price' => $service->price,
                        'wholesale_applicable' => $service->wholesale_price > 0 ? 1 : 0,
                        'wholesale_price' => $service->wholesale_price,
                        'stock' => $service->stock,
                        'unit' => $service->unit,
                        'discount_applicable' => $service->discount() ? true : false,
                        'discounted_price' => $service->discount() ? $service->getDiscountedAmount() : 0,
                        'vat_percentage' => $service->vat_percentage,
                        'is_published_for_shop' => (int)$service->is_published_for_shop,
                        'warranty' => (double)$service->warranty,
                        'warranty_unit' => $service->warranty_unit ? config('pos.warranty_unit')[$service->warranty_unit] : null
                    ];
                });
            if (!$services) return api_response($request, null, 404);

            return api_response($request, $services, 200, ['services' => $services]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param $service
     * @param Request $request
     * @return JsonResponse
     */
    public function show($partner, $service, Request $request)
    {
        try {
            $service = PartnerPosService::with('category', 'discounts')->find($service);
            if (!$service) return api_response($request, null, 404);
            $partner = $service->partner;
            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $resource = new Item($service, new PosServiceTransformer());
            $service = $manager->createData($resource)->toArray();

            return api_response($request, $service, 200, ['service' => $service, 'partner' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'logo' => $partner->logo
            ]]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param ProductCreator $creator
     * @return JsonResponse
     */
    public function store(Request $request, ProductCreator $creator)
    {
        try {
            $secondaries_categories = PosCategory::child()->pluck('id')->toArray();
            $this->validate($request, [
                'name' => 'required',
                'category_id' => 'required|in:' . implode(',', $secondaries_categories),
                'price' => 'required',
                'unit' => 'sometimes|in:' . implode(',', array_keys(constants('POS_SERVICE_UNITS')))
            ]);
            $this->setModifier($request->partner);
            $partner_pos_service = $creator->setData($request->all())->create();

            if ($request->has('discount_amount') && $request->discount_amount > 0) {
                $this->createServiceDiscount($request, $partner_pos_service);
            }
            $partner_pos_service->unit = $partner_pos_service->unit ? constants('POS_SERVICE_UNITS')[$partner_pos_service->unit] : null;
            $partner_pos_service->warranty_unit = $partner_pos_service->warranty_unit ? config('pos.warranty_unit')[$partner_pos_service->warranty_unit] : null;

            app()->make(ActionRewardDispatcher::class)->run('pos_inventory_create', $request->partner, $request->partner, $partner_pos_service);

            return api_response($request, null, 200, ['msg' => 'Product Created Successfully', 'service' => $partner_pos_service]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param ProductUpdater $updater
     * @param PosServiceDiscountRepository $discount_repo
     * @return JsonResponse
     */
    public function update(Request $request, ProductUpdater $updater, PosServiceDiscountRepository $discount_repo)
    {
        try {
            $rules = ['unit' => 'sometimes|in:' . implode(',', array_keys(constants('POS_SERVICE_UNITS')))];
            if ($request->has('discount_amount') && $request->discount_amount > 0) $rules += ['end_date' => 'required'];
            $this->validate($request, $rules);
            $this->setModifier($request->partner);
            $partner_pos_service = PartnerPosService::find($request->service);
            if (!$partner_pos_service) return api_response($request, null, 400, ['msg' => 'Service Not Found']);
            $updater->setService($partner_pos_service)->setData($request->all())->update();

            if ($request->discount_id) {
                $discount_data = [];
                $discount = PartnerPosServiceDiscount::find($request->discount_id);
                if ($request->has('is_discount_off') && $request->is_discount_off == 'true') {
                    $discount_data = ['end_date' => Carbon::now()];
                } else {
                    $requested_end_date = ($request->has('end_date')) ? Carbon::parse($request->end_date . ' 23:59:59') : $discount->end_date;
                    if ($request->has('end_date') && !$requested_end_date->isSameDay($discount->end_date)) {
                        $discount_data['end_date'] = $requested_end_date;
                    }

                    if ($request->has('discount_amount') && $request->discount_amount != $discount->amount) {
                        $discount_data['amount'] = (double)$request->discount_amount;
                    }
                }

                if (!empty($discount_data)) $discount_repo->update($discount, $discount_data);
            }

            if ($request->is_discount_off == 'false' && !$request->discount_id) {
                $this->createServiceDiscount($request, $partner_pos_service);
            }
            $partner_pos_service->unit = $partner_pos_service->unit ? constants('POS_SERVICE_UNITS')[$partner_pos_service->unit] : null;
            $partner_pos_service->warranty_unit = $partner_pos_service->warranty_unit ? config('pos.warranty_unit')[$partner_pos_service->warranty_unit] : null;

            return api_response($request, null, 200, ['msg' => 'Product Updated Successfully', 'service' => $partner_pos_service]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param Deleter $deleter
     * @return JsonResponse
     */
    public function destroy(Request $request, Deleter $deleter)
    {
        try {
            $this->setModifier($request->partner);
            $deleter->delete($request->service);

            return api_response($request, null, 200, ['msg' => 'Product Updated Successfully']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUnits(Request $request)
    {
        try {
            $units = [];
            $all_units = constants('POS_SERVICE_UNITS');
            foreach ($all_units as $key => $unit) {
                array_push($units, $unit);
            }
            return api_response($request, $units, 200, ['units' => $units]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getWarrantyUnits(Request $request)
    {
        try {
            $warranty_units = [];
            $all_warranty_units = config('pos.warranty_unit');
            foreach ($all_warranty_units as $key => $unit) {
                array_push($warranty_units, $unit);
            }
            return api_response($request, $warranty_units, 200, ['warranty_units' => $warranty_units]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $partner
     * @param $service
     * @return JsonResponse
     */
    public function togglePublishForShopStatus(Request $request, $partner, $service)
    {
        $posService = PartnerPosService::query()->where([['id', $service], ['partner_id', $partner]])->first();
        if (empty($posService)) {
            return api_response($request, null, 404, ['message' => 'Requested service not found .']);
        }
        $posService->is_published_for_shop = !(int)$posService->is_published_for_shop;
        $posService->save();
        return api_response($request, null, 200, ['message' => 'Service successfully ' . ($posService->is_published_for_shop ? 'published' : 'unpublished')]);
    }

    /**
     * @param Request $request
     * @param PartnerPosService $partner_pos_service
     */
    private function createServiceDiscount(Request $request, PartnerPosService $partner_pos_service)
    {
        $discount_data = [
            'amount' => (double)$request->discount_amount,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::parse($request->end_date . ' 23:59:59')
        ];

        $partner_pos_service->discounts()->create($this->withCreateModificationField($discount_data));
    }

    private function getSelectColumnsOfService()
    {
        return ['id', 'name', 'app_thumb', 'app_banner', 'price', 'stock', 'vat_percentage', 'is_published_for_shop', 'warranty', 'warranty_unit', 'unit', 'wholesale_price'];
    }

    /**
     * @param Request $request
     * @param $partner
     * @param PartnerPosService $service
     * @return JsonResponse
     */
    public function getLogs(Request $request, $partner, PartnerPosService $service)
    {
        try {
            $logs = [];
            $identifier = [
                FieldType::STOCK => $unit_bn = $service->unit ? constants('POS_SERVICE_UNITS')[$service->unit]['bn']: 'একক',
                FieldType::VAT => '%',
                FieldType::PRICE => '৳',
            ];
            $service = $service->load('logs');
            $displayable_field_name = FieldType::getFieldsDisplayableNameInBangla();
            $service->logs->each(function ($log) use (&$logs, $displayable_field_name, $unit_bn, $identifier) {
                $log->field_names->each(function ($field) use (&$logs, $log, $displayable_field_name, $unit_bn, $identifier) {

                    if (!in_array($field, FieldType::fields())) return false;

                    array_push($logs, [
                        'log_type' => $field,
                        'log_type_show_name' => [
                            'bn' => $displayable_field_name[$field]['bn'],
                            'en' => $displayable_field_name[$field]['en']
                        ],
                        'log' => [
                            'bn' => $this->generateBanglaLog($field, $log, $identifier)
                        ],
                        'created_by' => $log->created_by_name,
                        'created_at' => $log->created_at->format('Y-m-d h:i a')
                    ]);
                });
            });

            return api_response($request, null, 200, ['logs' => $logs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $field
     * @param $log
     * @param array $identifier
     * @return string
     */
    public function generateBanglaLog($field, $log, array $identifier)
    {
        switch ($field) {
            case FieldType::STOCK:
                $log = convertNumbersToBangla($log->old_value->toArray()[$field]) . ' ' . $identifier[$field] . ' থেকে ' . convertNumbersToBangla($log->new_value->toArray()[$field]) . ' ' . $identifier[$field];
                break;
            case FieldType::PRICE:
                $log = $identifier[$field] . convertNumbersToBangla($log->old_value->toArray()[$field]) . ' থেকে ' . $identifier[$field] . convertNumbersToBangla($log->new_value->toArray()[$field]);
                break;
            case FieldType::VAT:
                $log = convertNumbersToBangla($log->old_value->toArray()[$field]) . $identifier[$field] . ' থেকে ' . convertNumbersToBangla($log->new_value->toArray()[$field]) . $identifier[$field];
                break;
            default:
                $log = "convertNumbersToBangla($log->old_value->toArray()[$field]) . ' ' . $identifier[$field] . ' থেকে ' . convertNumbersToBangla($log->new_value->toArray()[$field]) . ' ' . $identifier[$field]";
        }
        return $log;
    }
}
