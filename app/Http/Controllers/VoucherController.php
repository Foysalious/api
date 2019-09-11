<?php namespace App\Http\Controllers;

use App\Models\PosCustomer;
use App\Models\Promotion;
use App\Models\Voucher;
use App\Transformers\CustomSerializer;
use App\Transformers\PosOrderTransformer;
use App\Transformers\VoucherDetailTransformer;
use App\Transformers\VoucherTransformer;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\ModificationFields;
use Sheba\Voucher\DTO\Params\CheckParamsForPosOrder;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherRule;
use Throwable;

class VoucherController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function dashboard(Request $request)
    {
        try {
            $partner = $request->partner;

            $partner_voucher_query = Voucher::byPartner($partner);
            $latest_vouchers = [];
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());

            $data = [
                'total_voucher'     => $partner_voucher_query->count(),
                'active_voucher'    => $partner_voucher_query->valid()->count(),
                'total_sale_with_voucher' => 255648
            ];

            $partner_voucher_query->orderBy('id', 'desc')->take(3)->each(function ($voucher) use (&$latest_vouchers, $manager) {
                $resource = new Item($voucher, new VoucherTransformer());
                $voucher = $manager->createData($resource)->toArray();
                array_push($latest_vouchers, $voucher['data']) ;
            });

            return api_response($request, null, 200, ['data' => $data, 'latest_vouchers' => $latest_vouchers]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $partner = $request->partner;
            list($offset, $limit) = calculatePagination($request);
            $partner_voucher_query = Voucher::byPartner($partner);

            $used_voucher_id = [344467, 344466];

            if ($request->has('filter_type')) {
                if ($request->filter_type == "used") $partner_voucher_query->whereIn('id', $used_voucher_id);
                if ($request->filter_type == "valid") $partner_voucher_query->valid();
                if ($request->filter_type == "invalid") $partner_voucher_query->notValid();
            }
            if ($request->has('q') && !empty($request->q))
                $partner_voucher_query = $partner_voucher_query->search($request->q);

            $partner_voucher_query = $partner_voucher_query->skip($offset)->take($limit);

            $vouchers = [];

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $partner_voucher_query->orderBy('id', 'desc')->get()->each(function ($voucher) use (&$vouchers, $manager) {
                $resource = new Item($voucher, new VoucherTransformer());
                $voucher = $manager->createData($resource)->toArray();
                array_push($vouchers, $voucher['data']) ;
            });
            return api_response($request, null, 200, ['vouchers' => $vouchers]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $partner
     * @param Voucher $voucher
     * @return JsonResponse
     */
    public function show(Request $request, $partner, Voucher $voucher)
    {
        try {
            $partner = $request->partner;
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($voucher, new VoucherDetailTransformer());
            $formatted_voucher = $manager->createData($resource)->toArray();
            $formatted_voucher['data']['is_used'] = $voucher->usedCount() ? true : false;

            $data = [
                'total_used' => 5,
                'total_sale_with_voucher' => 256865,
                'total_discount_with_voucher' => 8500
            ];
            return api_response($request, null, 200, ['data' => $data, 'voucher' => $formatted_voucher['data']]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'code' => 'required|unique:vouchers'
            ]);

            $partner = $request->partner;
            $this->setModifier($partner);
            $data = [
                'code' => strtoupper($request->code),
                'rules' => $this->buildRules($request)->toJson(),
                'amount' => $request->amount,
                'cap' => $request->cap,
                'is_amount_percentage' => $request->is_amount_percentage,
                'start_date' => Carbon::parse($request->start_date . ' 00:00:00'),
                'end_date' => Carbon::parse($request->end_date . ' 23:59:59'),
                'max_customer' => ($request->has('max_customer') && !empty($request->max_customer)) ? $request->max_customer : null,
                'is_created_by_sheba' => 0,
                'sheba_contribution' => 0.00,
                'partner_contribution' => 100.00,
                'owner_type' => get_class($partner),
                'owner_id' => $partner->id,
                'created_by_type' => get_class($partner)
            ];
            $voucher = Voucher::create($this->withCreateModificationField($data));

            return api_response($request, null, 200, ['voucher' => $voucher]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    /**
     * @param Request $request
     * @param $partner
     * @param Voucher $voucher
     * @return JsonResponse
     */
    public function update(Request $request,$partner,Voucher $voucher)
    {
        try {
            $partner = $request->partner;
            $this->validate($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'code' => 'required|unique:vouchers'
            ]);

            $this->setModifier($partner);

            $data = [
                'code' => strtoupper($request->code),
                'rules' => $this->buildRules($request)->toJson(),
                'amount' => $request->amount,
                'cap' => $request->cap,
                'is_amount_percentage' => $request->is_amount_percentage,
                'start_date' => Carbon::parse($request->start_date . ' 00:00:00'),
                'end_date' => Carbon::parse($request->end_date . ' 23:59:59'),
                'max_customer' => ($request->has('max_customer') && !empty($request->max_customer)) ? $request->max_customer : null,
                'is_created_by_sheba' => 0,
                'sheba_contribution' => 0.00,
                'partner_contribution' => 100.00,
                'owner_type' => get_class($partner),
                'owner_id' => $partner->id,
                'created_by_type' => get_class($partner)
            ];

            $voucher->update($this->withUpdateModificationField($data));

            return api_response($request, null, 200, ['voucher' => $voucher]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return VoucherRule
     */
    private function buildRules(Request $request)
    {
        $rule = new VoucherRule();

        $modules = explode(',', $request->modules);
        $applicant_types = explode(',', $request->applicant_types);
        if ($request->has('modules')) $rule->setModules($modules);
        if ($request->has('applicant_types')) $rule->setApplicantTypes($applicant_types);
        if ($request->has('order_amount')) $rule->setOrderAmount($request->order_amount);
        if ($request->has('customers')) {
            $mobiles = explode(',', $request->customers);
            $rule->setMobiles($mobiles);
        }
        return $rule;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function validateVoucher(Request $request)
    {
        $pos_customer = PosCustomer::find($request->pos_customer);
        $pos_order_params = (new CheckParamsForPosOrder());
        $pos_order_params->setOrderAmount($request->amount)->setApplicant($pos_customer);
        $result = voucher($request->code)->checkForPosOrder($pos_order_params)->reveal();

        if ($result['is_valid']) {
            $voucher = $result['voucher'];
            $voucher = [
                'amount' => (double)$result['amount'],
                'code' => $voucher->code,
                'id' => $voucher->id,
                'title' => $voucher->title
            ];

            return api_response($request, null, 200, ['voucher' => $voucher]);
        } else {
            return api_response($request, null, 403, ['message' => 'Invalid Promo']);
        }
    }

    public function deactivateVoucher(Request $request,$partner,Voucher $voucher){
        try {
            $partner = $request->partner;
            $this->setModifier($partner);
            $voucher->end_date = Carbon::now();
            $voucher->update();
            return api_response($request, null, 200, ['msg' => 'Promo deactivated successfully']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }


    }
}