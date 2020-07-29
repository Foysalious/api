<?php

namespace App\Http\Controllers\Loan;

use App\Models\PartnerBankLoan;
use App\Sheba\Loan\DLSV2\Exceptions\InsufficientWalletCreditForRepayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\Loan\Exceptions\NotAllowedToAccess;
use Sheba\Loan\Loan;
use Sheba\Loan\LoanRepayments;
use Sheba\Loan\LoanRepository;
use Sheba\Payment\Adapters\Payable\LoanRepaymentAdapter;
use Sheba\Payment\AvailableMethods;
use Sheba\Payment\PaymentManager;
use Throwable;

class RepaymentController extends Controller
{
    public function init(Request $request, LoanRepaymentAdapter $adapter, $partner, $loan_id, LoanRepository $repo, PaymentManager $manager)
    {
        try {
            $methods = implode(',', AvailableMethods::getLoanRepaymentPayments());
            $this->validate($request, [
                'payment_method' => 'required|in:' . $methods,
                'amount'         => 'required|numeric|min:10|max:100000',
            ]);
            /** @var PartnerBankLoan $loan */
            $loan    = $repo->find($loan_id);
            $method  = $request->payment_method;
            $payable = $adapter->setAmount((double)$request->amount)->setLoan($loan)->getPayable();
            $payment = $manager->setMethodName($method)->setPayable($payable)->init();
            return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors());
            return api_response($request, null, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param                $partner
     * @param                $loan_id
     * @param LoanRepayments $loanRepayments
     * @param Loan $loan
     * @return JsonResponse
     */
    public function repaymentList(Request $request, $partner, $loan_id, LoanRepayments $loanRepayments, Loan $loan)
    {
        try {
            $this->validate($request, [
                'month' => 'required|numeric',
                'year' => 'required|numeric'
            ]);
            $request->merge(['loan_id' => $loan_id]);
            $loan->validateRequest($request);
            $data = $loanRepayments->repaymentList($loan_id, false, $request->year, $request->month);
            return api_response($request, null, 200, ['data' => $data]);
        } catch (NotAllowedToAccess $e) {
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
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
     * @param                $partner
     * @param                $loan_id
     * @param LoanRepayments $loanRepayments
     * @param Loan $loan
     * @return JsonResponse
     */
    public function repaymentFromWallet(Request $request, $partner, $loan_id, LoanRepayments $loanRepayments, Loan $loan)
    {
        try {
            $this->validate($request, [
                'amount' => 'required'
            ]);
            $partner  = $request->partner;
            $resource = $request->manager_resource;
            $request->merge(['loan_id' => $loan_id]);
            $loan->validateRequest($request);
            $loanRepayments->setPartner($partner)->setResource($resource)->repaymentFromWallet($request);
            return api_response($request, null, 200, ['message' => 'আপনার তথ্যাবলী যাচাইয়ের পর আপনার সাথে যোগাযোগ করা হবে।']);

        } catch (NotAllowedToAccess $e) {
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        } catch (InsufficientWalletCreditForRepayment $e) {
            return api_response($request, null, 403, ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
