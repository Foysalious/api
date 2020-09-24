<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\WithdrawalRequest;
use App\Sheba\BankingInfo\GeneralBanking;
use App\Sheba\UserRequestInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\WithdrawalRequest\RequesterTypes;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\ProfileBankingRepositoryInterface;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;
use Throwable;

class PartnerWithdrawalRequestV2Controller extends Controller
{

    use CdnFileManager, FileManager, ModificationFields;
    public function index($partner, Request $request)
    {
        try {
            $withdrawalRequests = $request->partner->withdrawalRequests->each(function ($item, $key) {
                $item['amount']       = (double)$item->amount;
                $item['requested_by'] = $item->created_by_name;
                removeSelectedFieldsFromModel($item);
            })->sortByDesc('id')->values()->all();
            $withdrawable_amount = $this->calculateWithdrawableAmount($request->partner);
            $bank_information = ($this->getBankInformation($request->manager_resource->profile));
            $limitBkash = constants('WITHDRAW_LIMIT')['bkash'];
            $limitBank  = constants('WITHDRAW_LIMIT')['bank'];
            $withdraw_limit = [
                'bkash' => [
                    'min' => $limitBkash['min'],
                    'max' => $limitBkash['max']
                ],
                'bank' => [
                    'min' => $limitBank['min'],
                    'max' => $limitBank['max']
                ]
            ];
            $banks = GeneralBanking::get();
            $security_money = ($request->partner->walletSetting->security_money ? floatval($request->partner->walletSetting->security_money) : 0);
            if (count($withdrawalRequests) > 0) {
                return api_response($request, $withdrawalRequests, 200,
                    ['withdrawalRequests' => $withdrawalRequests, 'wallet' => $request->partner->wallet, 'withdrawable_amount' => $withdrawable_amount,  'bank_info' => $bank_information , 'withdraw_limit' => $withdraw_limit,'security_money' => $security_money,'banks' => $banks, 'status_message' => 'আপনি গরিব']);
            } else {
                return api_response($request, null, 404, ['wallet' => $request->partner->wallet, 'withdrawable_amount' => $withdrawable_amount,  'bank_info' => $bank_information , 'withdraw_limit' => $withdraw_limit, 'security_money' => $security_money,'banks' => $banks ,'status_message' => 'আপনি গরিব']);
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
        $valid_maximum_requested_amount = (double)$partner->wallet - (double)$partner->walletSetting->security_money;
        if (((double)$request->amount > $valid_maximum_requested_amount)) {
            $message = "You don't have sufficient balance";
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
            return api_response($request, $withdrawal_update, 200);
        } else {
            return api_response($request, '', 403, ['result' => 'You can not update this withdraw request']);
        }
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
            return api_response($request, $withdrawal_update, 200);
        } else {
            return api_response($request, '', 403, ['result' => 'You can not update this withdraw request']);
        }
    }

    private function calculateWithdrawableAmount(Partner $partner)
    {
        $total_pending_amount           =  $partner->withdrawalRequests()->active()->sum('amount') ? : 0;
        return ($withdrawable_amount = ($partner->wallet - ($total_pending_amount + ($partner->walletSetting->security_money ? floatval($partner->walletSetting->security_money) : 0)))) > 0 ? $withdrawable_amount : 0;
    }

    private function getBankInformation(Profile $profile)
    {
        $info = $profile->banks()->where('purpose','partner_wallet_withdrawal')->orderBY('id','desc')->first();
        $bank_info = null;

        if($info){
            $bank_info =  [
                'bank_name' => $info->bank_name,
                'account_no' => $info->account_no,
                'account_type' => $info->account_type,
                'branch_name' =>  $info->branch_name,
                'routing_no' =>  $info->routing_no,
                'cheque_book_receipt' => $info->cheque_book_receipt,
            ];
        }
        return $bank_info;
    }

    public function storeBankInfo($partner, Request $request, ProfileBankingRepositoryInterface $profile_bank_repo)
    {
        try {
            $this->validate($request, [
                'bank_name' => 'required|in:'.implode(',', GeneralBanking::get()),
                'account_no' => 'required',
                'account_type' => 'required|in:savings,current',
                'branch_name' => 'required',
                'routing_no' => 'required_',
                'cheque_book_receipt' => 'sometimes|required|file|mimes:jpg,jpeg,png',

            ]);
            $cheque_book_receipt = null;
            if($request->hasFile('cheque_book_receipt')){
                list($cheque_book_receipt, $cheque_book_receipt_filename) = $this->makeChequeBookReceipt($request->cheque_book_receipt, $request->partner->id.'cheque_book_receipt');
                $cheque_book_receipt = $this->saveImageToCDN($cheque_book_receipt, getPartnerChequeBookImageFolder(), $cheque_book_receipt_filename);
            }

            $data = [
                'profile_id' => $request->manager_resource->profile->id,
                'bank_name' => $request->bank_name,
                'account_no' => $request->account_no,
                'account_type' => $request->account_type,
                'branch_name' => $request->branch_name,
                'routing_no' => $request->routing_no,
                'cheque_book_receipt' => $cheque_book_receipt,
                'purpose' => 'partner_wallet_withdrawal'
            ];
            $this->setModifier($request->manager_resource);
            $profile_bank_repo->create($data);
            return api_response($request, null, 200,['message' => 'Bank Information stored successfully']);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }
}
