<?php


namespace App\Http\Controllers\Loan;


use App\Http\Controllers\Controller;
use App\Sheba\Loan\DLSV2\Exceptions\NotEligibleForClaim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Loan\Exceptions\NotAllowedToAccess;
use Sheba\Loan\Loan;
use Throwable;

class ClaimController extends Controller
{
    /**
     * @param Request $request
     * @param $partner
     * @param $loan_id
     * @param Loan $loan
     * @return JsonResponse
     */
    public function claim(Request $request, $partner, $loan_id, Loan $loan)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric|min:1000'
            ]);
            $request->merge(['loan_id' => $loan_id]);
            $loan->validateRequest($request);

            if(!$loan->canClaim($request))
                throw new NotEligibleForClaim();
            $loan->claim($request);
            return api_response($request, null, 200, ['message' => 'আপনার টাকা দাবির আবেদনটি সফলভাবে গৃহীত হয়েছে। সকল তথ্য যাচাই করার পর আপনার বন্ধু আকাউন্টে টাকা জমা হয়ে যাবে।']);
        } catch (NotAllowedToAccess $e) {
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        } catch (NotEligibleForClaim $e) {
            return api_response($request, null, 403, ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => 'িছু একটা সমস্যা হয়েছে যার কারণে আপনার আবেদনটি জমা দেয়া সম্ভব হয়নি']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, ['message' => 'িছু একটা সমস্যা হয়েছে যার কারণে আপনার আবেদনটি জমা দেয়া সম্ভব হয়নি']);
        }

    }

    /**
     * @param Request $request
     * @param $partner
     * @param $loan_id
     * @param Loan $loan
     * @return JsonResponse
     */
    public function claimList(Request $request, $partner, $loan_id, Loan $loan)
    {
        try {
            $this->validate($request, [
                'month' => 'required|numeric',
                'year' => 'required|numeric'
            ]);
            $request->merge(['loan_id' => $loan_id]);
            $loan->validateRequest($request);
            $data = $loan->claimList($loan_id, false, $request->year, $request->month);
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
     * @param $loan_id
     * @param Loan $loan
     * @return JsonResponse
     */
    public function claimListForPortal(Request $request, $loan_id, Loan $loan)
    {
        try {
            $request->merge(['loan_id' => $loan_id]);
            $data = $loan->claimList($loan_id, true);
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
     * @param $loan_id
     * @param Loan $loan
     * @return JsonResponse
     */
    public function claimStatusUpdate(Request $request, $loan_id, Loan $loan)
    {
        try {
            $this->validate($request, [
                'claim_id' => 'required',
                'from' => 'required|in:pending,approved,declined',
                'to' => 'required|in:pending,approved,declined'
            ]);
            $request->merge(['loan_id' => $loan_id]);
            $loan->claimStatusUpdate($request);
            return api_response($request, null, 200,['data' => ["code" => 200]]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => 'িছু একটা সমস্যা হয়েছে যার কারণে আপনার আবেদনটি জমা দেয়া সম্ভব হয়নি']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param Request $request
     * @param $partner
     * @param $loan_id
     * @param Loan $loan
     * @return JsonResponse
     */
    public function approvedClaimMsgSeen(Request $request, $partner, $loan_id, Loan $loan)
    {
        try {
            $this->validate($request, [
                'success_msg_seen' => 'required|in:0,1',
            ]);
            $request->merge(['loan_id' => $loan_id]);
            $loan->validateRequest($request);
            $loan->approvedClaimMsgSeen($request);
            return api_response($request, null, 200);
        } catch (NotAllowedToAccess $e) {
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}
