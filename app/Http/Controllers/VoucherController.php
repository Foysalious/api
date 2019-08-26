<?php namespace App\Http\Controllers;

use App\Models\Voucher;
use Carbon\Carbon;
use Folklore\GraphQL\Error\ValidationError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'code' => 'required|unique:vouchers'
            ]);

            $data = [
                'code' => strtoupper($request->code),
                'rules' => $this->buildRules($request)->toJson(),
                'amount' => $request->amount,
                'cap' => $request->cap,
                'is_amount_percentage' => $request->is_amount_percentage,
                'start_date' => Carbon::parse($request->start_date . ' 00:00:00'),
                'end_date' => Carbon::parse($request->end_date . ' 23:59:59'),
                'is_created_by_sheba' => 0,
                'sheba_contribution' => 0.00,
                'partner_contribution' => 100.00
            ];
            $voucher = Voucher::create($this->withBothModificationFields($data));
            if ($request->has('tag_list')) $voucher->tags()->sync($request->tag_list);

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