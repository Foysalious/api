<?php namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\WithdrawalRequest;
use App\Sheba\UserRequestInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\WithdrawalRequest\RequesterTypes;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;
use Throwable;
use Validator;

class PartnerWithdrawalRequestController extends Controller
{
    public function index($partner, Request $request)
    {
        try {
            $withdrawalRequests = $request->partner->withdrawalRequests->each(function ($item, $key) {
                $item['amount']       = (double)$item->amount;
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param                    $partner
     * @param Request            $request
     * @param ShebaAccountKit    $sheba_account_kit
     * @param AccessTokenRequest $access_token_request
     * @return JsonResponse
     */
    public function store($partner, Request $request, ShebaAccountKit $sheba_account_kit, AccessTokenRequest $access_token_request)
    {
        $this->validate($request, [
            'amount'         => 'required|numeric',
            'payment_method' => 'required|in:bkash,bank',
            'bkash_number'   => 'required_if:payment_method,bkash|mobile:bd',
            'code'           => 'required_if:payment_method,bkash|string'
        ]);

        /** @var Partner $partner */
        $partner = $request->partner;
        if ($request->payment_method != 'bank') {
            if (
                ($request->header('portal-name') && $request->header('portal-name') == 'partner-portal') ||
                $request->header('version-code') && $request->header('version-code') > 21104
            ) {
                $access_token_request->setAuthorizationCode($request->code);
                $authenticate_data['mobile'] = $sheba_account_kit->getMobile($access_token_request);
            } else {
                /**
                 * NUMBER MATCH VALIDATIONS BY FACEBOOK ACCOUNT KIT
                 */
                $authenticate_data = (new FacebookAccountKit())->authenticateKit($request->code);
            }

            if (trim_phone_number($request->bkash_number) != trim_phone_number($authenticate_data['mobile'])) {
                return api_response($request, null, 400, ['message' => 'Your provided bkash number and verification number did not match,please verify using your bkash number']);
            }
        }


        /**
         * Limit Validation
         */
        $limitBkash = constants('WITHDRAW_LIMIT')['bkash'];
        $limitBank  = constants('WITHDRAW_LIMIT')['bank'];
        if ($request->payment_method == 'bkash' && ((double)$request->amount < $limitBkash['min'] || (double)$request->amount > $limitBkash['max'])) {
            return api_response($request, null, 400, ['message' => 'Payment Limit mismatch for bkash minimum limit ' . $limitBkash['min'] . ' TK and maximum ' . $limitBkash['max'] . ' TK']);
        } else if ($request->payment_method == 'bank' && ((double)$request->amount < $limitBank['min'] || (double)$request->amount > $limitBank['max'])) {
            return api_response($request, null, 400, ['message' => 'Payment Limit mismatch for bank minimum limit ' . $limitBank['min'] . ' TK and maximum ' . $limitBank['max'] . ' TK']);
        }

        $allowed_to_send_request        = $partner->isAllowedToSendWithdrawalRequest();
        $valid_maximum_requested_amount = (double)$partner->wallet - (double)$partner->walletSetting->security_money;

        if (!$allowed_to_send_request || ((double)$request->amount > $valid_maximum_requested_amount)) {
            if (!$allowed_to_send_request) $message = "You have already sent a Withdrawal Request";
            else $message = "You don't have sufficient balance";
            return api_response($request, null, 403, ['message' => $message]);
        }
        $new_withdrawal = WithdrawalRequest::create(array_merge((new UserRequestInformation($request))->getInformationArray(), [
            'requester_id'    => $partner->id,
            'requester_type'  => RequesterTypes::PARTNER,
            'amount'          => $request->amount,
            'payment_method'  => $request->payment_method,
            'payment_info'    => json_encode(['bkash_number' => $request->payment_method != 'bkash' ?: $request->bkash_number]),
            'created_by_type' => class_basename($request->manager_resource),
            'created_by'      => $request->manager_resource->id,
            'created_by_name' => 'Resource - ' . $request->manager_resource->profile->name,
        ]));

        return api_response($request, $new_withdrawal, 200);
    }

    public function update($partner, $withdrawals, Request $request)
    {
        $this->validate($request, ['status' => 'required|in:cancelled']);
        $partner                  = $request->partner;
        $partnerWithdrawalRequest = WithdrawalRequest::find($withdrawals);
        if (($partner->id == $partnerWithdrawalRequest->requester->id) && ($partnerWithdrawalRequest->requester_type=='partner') && ($partnerWithdrawalRequest->status == 'pending')) {
            $withdrawal_update = $partnerWithdrawalRequest->update([
                'status'          => $request->status,
                'updated_by'      => $request->manager_resource->id,
                'updated_by_name' => 'Resource - ' . $request->manager_resource->profile->name,
            ]);
            if ($request->status == 'cancelled') {
                $partner->walletSetting->update(['pending_withdrawal_amount' => $partner->walletSetting->pending_withdrawal_amount - $partnerWithdrawalRequest->amount]);
            }
            return api_response($request, $withdrawal_update, 200);
        } else {
            return api_response($request, '', 403, ['result' => 'You can not update this withdraw request']);
        }
    }

    public function getStatus($partner, Request $request)
    {
        $partner = $request->partner;
        list($can_withdraw, $status) = $this->canWithdraw($partner);
        return api_response($request, $status, 200, ['status' => $status, 'can_withdraw' => $can_withdraw]);
    }

    public function cancel($partner, $withdrawals, Request $request)
    {
        $partner = $request->partner;
        /** @var WithdrawalRequest $partnerWithdrawalRequest */
        $partnerWithdrawalRequest = WithdrawalRequest::find($withdrawals);
        if (($partner->id == $partnerWithdrawalRequest->requester->id) && ($partnerWithdrawalRequest->requester_type=='partner') && ($partnerWithdrawalRequest->status == 'pending')) {
            $withdrawal_update = $partnerWithdrawalRequest->update([
                'status'          => 'cancelled',
                'updated_by'      => $request->manager_resource->id,
                'updated_by_name' => 'Resource - ' . $request->manager_resource->profile->name,
            ]);
            if ($request->status == 'cancelled') {
                $partner->walletSetting->update(['pending_withdrawal_amount' => $partner->walletSetting->pending_withdrawal_amount - $partnerWithdrawalRequest->amount]);
            }
            return api_response($request, $withdrawal_update, 200);
        } else {
            return api_response($request, '', 403, ['result' => 'You can not update this withdraw request']);
        }
    }

    private function canWithdraw(Partner $partner)
    {
        $active_withdrawal_request           = $partner->withdrawalRequests()->pending()->first();
        $does_wallet_have_sufficient_balance = $partner->wallet > $partner->walletSetting->security_money;
        $can_withdraw                        = true;
        $status                              = 'You can send withdraw request';
        if (!$does_wallet_have_sufficient_balance) {
            $status       = 'You don\'t have sufficient balance on your wallet. So you can\'t send a withdraw request.';
            $can_withdraw = false;
        } elseif ($does_wallet_have_sufficient_balance && $active_withdrawal_request) {
            $status       = 'We have received your withdrawal request, Please wait for approval.';
            $can_withdraw = false;
        }
        return [$can_withdraw, $status];
    }
}
