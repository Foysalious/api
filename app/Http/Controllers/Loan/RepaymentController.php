<?php

namespace App\Http\Controllers\Loan;

use App\Sheba\Loan\DLSV2\Exceptions\InsufficientWalletCreditForRepayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\Loan\LoanRepayments;
use Sheba\Payment\AvailableMethods;

class RepaymentController extends Controller
{
    public function init(Request $request){
        $methods = implode(',', AvailableMethods::getLoanRepaymentPayments());
        $this->validate($request, [
            'payment_method' => 'required|in:' . $methods,
            'amount'         => 'required|numeric|min:10|max:100000'
        ]);
    }

    /**
     * @param Request        $request
     * @param                $partner
     * @param                $loan_id
     * @param LoanRepayments $loanRepayments
     * @return JsonResponse
     */
    public function repaymentList(Request $request, $partner, $loan_id, LoanRepayments $loanRepayments)
    {
        try{
            $this->validate($request,[
                'month' => 'required|numeric',
                'year' => 'required|numeric'
            ]);
            $request->merge(['loan_id' => $loan_id]);
            $data = $loanRepayments->repaymentList($loan_id,false, $request->year, $request->month);
            return api_response($request, null, 200, ['data' => $data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400,['message' => $message]);
        } catch (Throwable $e){
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request        $request
     * @param                $partner
     * @param                $loan_id
     * @param LoanRepayments $loanRepayments
     * @return JsonResponse
     */
    public function repaymentFromWallet(Request $request, $partner, $loan_id, LoanRepayments $loanRepayments)
    {
        try {
            $this->validate($request, [
                'amount' => 'required'
            ]);
            $partner = $request->partner;
            $resource = $request->manager_resource;
            $request->merge(['loan_id' => $loan_id]);
            $loanRepayments->setPartner($partner)->setResource($resource)->repaymentFromWallet($request);
            return api_response($request, null, 200, ['message' => 'আপনার তথ্যাবলী যাচাইয়ের পর আপনার সাথে যোগাযোগ করা হবে।']);

        } catch (InsufficientWalletCreditForRepayment $e) {
            return api_response($request, null, 403, ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
