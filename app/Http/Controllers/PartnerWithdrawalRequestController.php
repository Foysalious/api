<?php


namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerWithdrawalRequest;
use App\Sheba\UserRequestInformation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;

class PartnerWithdrawalRequestController extends Controller
{
    public function index($partner, Request $request)
    {
        try {
            $withdrawalRequests = $request->partner->withdrawalRequests->each(function ($item, $key) {
                $item['amount'] = (double)$item->amount;
                $item['requested_by'] = $item->created_by_name;
                removeSelectedFieldsFromModel($item);
            })->sortByDesc('id')->values()->all();
            list($can_withdraw, $status) = $this->canWithdraw($request->partner);
            if (count($withdrawalRequests) > 0) {
                return api_response($request, $withdrawalRequests, 200,
                    ['withdrawalRequests' => $withdrawalRequests, 'wallet' => $request->partner->wallet, 'can_withdraw' => $can_withdraw, 'status' => $status]);
            } else {
                return api_response($request, null, 404, ['can_withdraw' => $can_withdraw, 'status' => $status, 'wallet' => $request->partner->wallet]);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($partner, Request $request)
    {
        try {
            $this->validate($request, ['amount' => 'required|numeric', 'payment_method' => 'required|in:bkash,bank', 'bkash_number' => 'required_if:payment_method,bkash|mobile:bd', 'code' => 'required_if:payment_method,bkash|string']);
            $partner = $request->partner;
//            Number Match validations
            $authenticate_data = (new FacebookAccountKit())->authenticateKit($request->code);
            if (trim_phone_number($request->bkash_number) != trim_phone_number($authenticate_data['mobile'])) {
                return api_response($request, null, 400, ['message' => 'Your provided bkash number and verification number did not match,please verify using your bkash number']);
            }
//            Limit Validation
            $minLimit = constants('WITHDRAW_LIMIT')['bkash']['min'];
            $maxLimit = constants('WITHDRAW_LIMIT')['bkash']['max'];
            if ($request->payment_method == 'bkash' && ((double)$request->amount < $minLimit || (double)$request->amount > $maxLimit)) {
                return api_response($request, null, 400, ['message' => 'Payment Limit mismatch minimum limit ' . $minLimit . ' TK and maximum ' . $maxLimit . ' TK']);
            } else if ($request->payment_method == 'bank' && ((double)$request->amount < $maxLimit)) {
                return api_response($request, null, 400, ['message' => 'For Bank Transaction minimum limit is ' . $maxLimit]);
            }
            $activePartnerWithdrawalRequest = $partner->withdrawalRequests()->pending()->first();
            $valid_maximum_requested_amount = (double)$partner->wallet - (double)$partner->walletSetting->security_money;
            if ($activePartnerWithdrawalRequest || ($request->amount > $valid_maximum_requested_amount)) {
                if ($activePartnerWithdrawalRequest) $message = "You have already sent a Withdrawal Request";
                else $message = "You don't have sufficient balance";
                return api_response($request, null, 403, ['message' => $message]);
            }
            $new_withdrawal = PartnerWithdrawalRequest::create(array_merge((new UserRequestInformation($request))->getInformationArray(), [
                'partner_id' => $partner->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_info' => json_encode(['bkash_number' => $request->payment_method != 'bkash' ?: $request->bkash_number]),
                'created_by_type' => class_basename($request->manager_resource),
                'created_by' => $request->manager_resource->id,
                'created_by_name' => 'Resource - ' . $request->manager_resource->profile->name,
            ]));
            return api_response($request, $new_withdrawal, 200);
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

    public function update($partner, $withdrawals, Request $request)
    {
        try {
            $this->validate($request, ['status' => 'required|in:cancelled']);
            $partner = $request->partner;
            $partnerWithdrawalRequest = PartnerWithdrawalRequest::find($withdrawals);
            if ($partner->id == $partnerWithdrawalRequest->partner_id && $partnerWithdrawalRequest->status == constants('PARTNER_WITHDRAWAL_REQUEST_STATUSES')['pending']) {
                $withdrawal_update = $partnerWithdrawalRequest->update([
                    'status' => $request->status,
                    'updated_by' => $request->manager_resource->id,
                    'updated_by_name' => 'Resource - ' . $request->manager_resource->profile->name,
                ]);
                return api_response($request, $withdrawal_update, 200);
            } else {
                return api_response($request, '', 403, ['result' => 'You can not update this withdraw request']);
            }
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

    public function getStatus($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            list($can_withdraw, $status) = $this->canWithdraw($partner);
            return api_response($request, $status, 200, ['status' => $status, 'can_withdraw' => $can_withdraw]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function cancel($partner, $withdrawals, Request $request)
    {
        try {
//            $this->validate($request, ['status' => 'required|in:cancelled']);
            $partner = $request->partner;
            $partnerWithdrawalRequest = PartnerWithdrawalRequest::find($withdrawals);
            if ($partner->id == $partnerWithdrawalRequest->partner_id && $partnerWithdrawalRequest->status == constants('PARTNER_WITHDRAWAL_REQUEST_STATUSES')['pending']) {
                $withdrawal_update = $partnerWithdrawalRequest->update([
                    'status' => 'cancelled',
                    'updated_by' => $request->manager_resource->id,
                    'updated_by_name' => 'Resource - ' . $request->manager_resource->profile->name,
                ]);
                return api_response($request, $withdrawal_update, 200);
            } else {
                return api_response($request, '', 403, ['result' => 'You can not update this withdraw request']);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function canWithdraw(Partner $partner)
    {
        $activePartnerWithdrawalRequest = $partner->withdrawalRequests()->pending()->first();
        $is_wallet_has_sufficient_balance = $partner->wallet > $partner->walletSetting->security_money;
        $can_withdraw = true;
        $status = 'You can send withdraw request';
        if (!$is_wallet_has_sufficient_balance) {
            $status = 'You don\'t have sufficient balance on your wallet. So you can\'t send a withdraw request.';
            $can_withdraw = false;
        } elseif ($is_wallet_has_sufficient_balance && $activePartnerWithdrawalRequest) {
            $status = 'We have received your withdrawal request, Please wait for approval.';
            $can_withdraw = false;
        }
        return array($can_withdraw, $status);
    }


}