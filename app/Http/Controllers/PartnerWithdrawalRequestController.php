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
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            $partner = $request->partner;
            $activePartnerWithdrawalRequest = $partner->withdrawalRequests()->currentWeek()->notCancelled()->first();
            $valid_maximum_requested_amount = (double)$partner->wallet - (double)$partner->walletSetting->security_money;
            if ($activePartnerWithdrawalRequest || ($request->amount > $valid_maximum_requested_amount)) {
                $result = 'You are not eligible for sending withdraw request.';
                return api_response($request, $result, 403, ['result' => $result]);
            }
            $new_withdrawal = PartnerWithdrawalRequest::create(array_merge((new UserRequestInformation($request))->getInformationArray(), [
                'partner_id' => $partner->id,
                'amount' => $request->amount,
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
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:cancelled'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
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

    private function canWithdraw(Partner $partner)
    {
        $activePartnerWithdrawalRequest = $partner->withdrawalRequests()->currentWeek()->notCancelled()->first();
        $is_wallet_has_sufficient_balance = $partner->wallet > $partner->walletSetting->security_money;
        $can_withdraw = true;
        $status = 'You can send withdraw request';
        if (!$is_wallet_has_sufficient_balance) {
            $status = 'You don\'t have sufficient balance on your wallet. So you can\'t send a withdraw request.';
            $can_withdraw = false;
        } elseif ($is_wallet_has_sufficient_balance && $activePartnerWithdrawalRequest) {
            $status = 'You have already sent a Withdrawal Request';
            $can_withdraw = false;
        }
        return array($can_withdraw, $status);
    }


}