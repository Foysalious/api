<?php


namespace App\Http\Controllers;


use App\Models\PartnerWithdrawalRequest;
use Illuminate\Http\Request;
use Validator;

class PartnerWithdrawalRequestController extends Controller
{
    public function index($partner, Request $request)
    {
        try {
            $withdrawalRequests = $request->partner->withdrawalRequests->each(function ($item, $key) {
                $item['amount'] = (double)$item->amount;
                removeSelectedFieldsFromModel($item);
            })->sortByDesc('id')->values()->all();
            if (count($withdrawalRequests) > 0) {
                return api_response($request, $withdrawalRequests, 200, ['withdrawalRequests' => $withdrawalRequests]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
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
            $new_withdrawal = PartnerWithdrawalRequest::create([
                'partner_id' => $partner->id,
                'amount' => $request->amount,
                'created_by' => $request->manager_resource->id,
                'created_by_name' => 'Resource - ' . $request->manager_resource->profile->name,
            ]);
            return api_response($request, $new_withdrawal, 200);
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getStatus($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $activePartnerWithdrawalRequest = $partner->withdrawalRequests()->currentWeek()->notCancelled()->first();
            $is_wallet_has_sufficient_balance = ((double)$partner->wallet > (double)$partner->walletSetting->security_money);
            $can_withdraw = true;
            $status = 'You can send withdraw request';
            if (!$is_wallet_has_sufficient_balance) {
                $status = 'You don\'t have sufficient balance on your wallet. So you can\'t send a withdraw request.';
                $can_withdraw = false;
            } elseif ($is_wallet_has_sufficient_balance && $activePartnerWithdrawalRequest) {
                $status = 'You have already sent a Withdrawal Request';
                $can_withdraw = false;
            }
            return api_response($request, $status, 200, ['status' => $status, 'can_withdraw' => $can_withdraw]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }


}