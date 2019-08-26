<?php namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Transformers\CustomSerializer;
use App\Transformers\PosOrderTransformer;
use App\Transformers\VoucherDetailTransformer;
use App\Transformers\VoucherTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\ModificationFields;
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
            $partner_voucher_query->orderBy('id', 'desc')->take(3)->each(function ($voucher) use (&$latest_vouchers, $manager) {
                $resource = new Item($voucher, new VoucherTransformer());
                $voucher = $manager->createData($resource)->toArray();
                array_push($latest_vouchers, $voucher['data']) ;
            });
            $data = [
                'total_voucher'     => $partner_voucher_query->count(),
                'active_voucher'    => $partner_voucher_query->valid()->count(),
                'total_sale_with_voucher' => 255648
            ];
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
            $partner_voucher_query = Voucher::byPartner($partner);
            if ($request->has('is_valid') && $request->is_valid) $partner_voucher_query->valid();
            if ($request->has('is_valid') && !$request->is_valid) $partner_voucher_query->notValid();
            $vouchers = [];

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $partner_voucher_query->orderBy('id', 'desc')->each(function ($voucher) use (&$vouchers, $manager) {
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
            $voucher = $manager->createData($resource)->toArray();

            return api_response($request, null, 200, ['voucher' => $voucher['data']]);
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

        return $rule;
    }
}