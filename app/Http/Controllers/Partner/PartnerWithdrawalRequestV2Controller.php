<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Models\Partner;
use App\Models\PartnerBankInformation;
use App\Models\WithdrawalRequest;
use App\Sheba\UserRequestInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Dal\PartnerBankInformation\Purposes;
use Sheba\Dal\WithdrawalRequest\RequesterTypes;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Partner\PartnerStatuses;
use Sheba\PartnerWithdrawal\PartnerWithdrawalService;
use Sheba\Repositories\Interfaces\ProfileBankingRepositoryInterface;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;

class PartnerWithdrawalRequestV2Controller extends Controller
{
    use CdnFileManager, FileManager, ModificationFields;

    public function index($partner, Request $request)
    {
        $is_partner_blacklisted = false;
        $withdrawalRequests = $request->partner->withdrawalRequests->each(function ($item, $key) {
            $item['amount']       = (double)$item->amount;
            $item['requested_by'] = $item->created_by_name;
            removeSelectedFieldsFromModel($item);
        })->sortByDesc('id')->values()->all();
        $withdrawable_amount = $this->calculateWithdrawableAmount($request->partner);
        $bank_information = $this->getBankInformation($request->partner);
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
        $security_money = ($request->partner->walletSetting->security_money ? floatval($request->partner->walletSetting->security_money) : 0);

        if ($request->partner->withdrawalRequests()->active()->count() > 0) {
            $active_request_amount =  $request->partner->withdrawalRequests()->active()->sum('amount') ;
            if($withdrawable_amount > $limitBank['min']) {
                $error_message = 'আপনার '. convertNumbersToBangla($active_request_amount,true, 0) . ' টাকার উত্তোলনের আবেদন প্রক্রিয়াধীন রয়েছে। আপনি '.  convertNumbersToBangla($withdrawable_amount,true, 0). ' টাকা উত্তোলন করার জন্য আবেদন করতে পারবেন।';
            } else {
                $error_message = 'আপনার '.convertNumbersToBangla($active_request_amount,true, 0) . ' টাকার উত্তোলনের আবেদন প্রক্রিয়াধীন রয়েছে। পর্যাপ্ত ব্যালান্স না থাকার কারণে আপনি পুনরায় উত্তোলন করার জন্য আবেদন করতে পারবেন না। আপনার সিকিউরিটি মানি ৳'. convertNumbersToBangla($security_money, true, 0). '।';
            }
        } else {
            $error_message = 'পর্যাপ্ত ব্যালান্স না থাকার কারণে আপনি টাকা উত্তোলন এর জন্য আবেদন করতে পারবেন না। আপনার সিকিউরিটি মানি ৳'. convertNumbersToBangla($security_money, true, 0). '।';
        }

        if($request->partner->status === PartnerStatuses::BLACKLISTED || $request->partner->status === PartnerStatuses::PAUSED) {
            $error_message = 'ব্ল্যাক লিস্ট হওয়ার কারণে আপনি টাকা উত্তোলন এর জন্য আবেদন করতে পারবেন না।';
            $is_partner_blacklisted = true;
        }
        return api_response($request, $withdrawalRequests, 200, [
            'withdrawalRequests' => $withdrawalRequests,
            'wallet' => $request->partner->wallet,
            'withdrawable_amount' => $withdrawable_amount,
            'bank_info' => $bank_information ,
            'withdraw_limit' => $withdraw_limit,
            'security_money' => $security_money,
            'status_message' => $error_message,
            'is_black_listed' => $is_partner_blacklisted
        ]);
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
        if($partner->status === PartnerStatuses::BLACKLISTED || $partner->status === PartnerStatuses::PAUSED) {
            return api_response($request, null, 402, ['message' => 'ব্ল্যাক লিস্ট/সাময়িকভাবে বরখাস্ত হওয়ার কারণে আপনি টাকা উত্তোলন এর জন্য আবেদন করতে পারবেন না।আরও জানতে কল করুন ১৬৫১৬।']);
        }

        if ($request->payment_method == 'bkash')
        {
            $status_check = WithdrawalRequest::where('payment_method', 'bkash')->whereIn('status', ['pending', 'approval_pending'])->where('requester_id', $partner->id)->first();
            if ($status_check) {
                $message = 'ইতিমধ্যে আপনার ১ টি বিকাশের মাধ্যমে টাকা উত্তোলনের আবেদন প্রক্রিয়াধীন রয়েছে, অনুগ্রহ করে আবেদনটি সম্পূর্ণ হওয়া পর্যন্ত অপেক্ষা করুন অথবা ব্যাংকের মাধ্যমে টাকা উত্তোলনের আবেদন করুন।';
                return api_response($request, null, 402, ['message' => $message ]);
            }
        }

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

            /**
            Given mobile no and opt number( always 1st admin) not same
             */
//            if (trim_phone_number($request->bkash_number) != trim_phone_number($authenticate_data['mobile'])) {
//                return api_response($request, null, 400, ['message' => 'Your provided bkash number and verification number did not match,please verify using your bkash number']);
//            }
        }


        /**
         * Limit Validation
         */
        $limitBkash = constants('WITHDRAW_LIMIT')['bkash'];
        $limitBank  = constants('WITHDRAW_LIMIT')['bank'];
        $security_money = ($partner->walletSetting->security_money ? floatval($request->partner->walletSetting->security_money) : 0);
        if ($request->payment_method == 'bkash' && ((double)$request->amount < $limitBkash['min'] || (double)$request->amount > $limitBkash['max'])) {
            return api_response($request, null, 400, ['message' => 'Payment Limit mismatch for bkash minimum limit ' . $limitBkash['min'] . ' TK and maximum ' . $limitBkash['max'] . ' TK']);
        } else if ($request->payment_method == 'bank' && ((double)$request->amount < $limitBank['min'] || (double)$request->amount > $limitBank['max'])) {
            return api_response($request, null, 400, ['message' => 'Payment Limit mismatch for bank minimum limit ' . $limitBank['min'] . ' TK and maximum ' . $limitBank['max'] . ' TK']);
        }
        $valid_maximum_requested_amount = (double)$partner->wallet - (double)$partner->walletSetting->security_money- (double)$partner->withdrawalRequests()->active()->sum('amount');
        if (((double)$request->amount > $valid_maximum_requested_amount)) {
            $message = 'পর্যাপ্ত ব্যালান্স না থাকার কারণে আপনি টাকা উত্তোলন এর জন্য আবেদন করতে  পারবেন না।আপনার সিকিউরিটি মানি ৳'. convertNumbersToBangla($security_money, true, 0). '।';
            return api_response($request, null, 403, ['message' => $message]);
        }
        $data = array_merge((new UserRequestInformation($request))->getInformationArray(), [
            'requester_id'    => $partner->id,
            'requester_type'  => RequesterTypes::PARTNER,
            'amount'          => $request->amount,
            'payment_method'  => $request->payment_method,
            'payment_info'    => json_encode(['bkash_number' => $request->payment_method != 'bkash' ?: $request->bkash_number]),
            'created_by_type' => class_basename($request->manager_resource),
            'created_by'      => $request->manager_resource->id,
            'created_by_name' => 'Resource - ' . $request->manager_resource->profile->name,
            'api_request_id' => $request->api_request ? $request->api_request->id : null,
            'wallet_balance' => $partner->wallet
        ]);
        /** @var PartnerWithdrawalService $partnerWithdrawalSvc */
        $partnerWithdrawalSvc = app(PartnerWithdrawalService::class);
        $new_withdrawal = $partnerWithdrawalSvc->store($partner, $data);

        return api_response($request, $new_withdrawal, 200);
    }

    public function update($partner, $withdrawals, Request $request)
    {
        $this->validate($request, ['status' => 'required|in:cancelled']);
        $partner                  = $request->partner;
        $partnerWithdrawalRequest = WithdrawalRequest::find($withdrawals);
        if (!$partnerWithdrawalRequest->isUpdateableByPartner($partner)) return api_response($request, '', 403, ['result' => 'You can not update this withdraw request']);

        $withdrawal_update = $partnerWithdrawalRequest->update([
            'status'          => $request->status,
            'reject_reason'   => $request->reject_reason,
            'updated_by'      => $request->manager_resource->id,
            'updated_by_name' => 'Resource - ' . $request->manager_resource->profile->name
        ]);
        return api_response($request, $withdrawal_update, 200);
    }

    public function cancel($partner, $withdrawals, Request $request)
    {
        $partner = $request->partner;
        /** @var WithdrawalRequest $partnerWithdrawalRequest */
        $partnerWithdrawalRequest = WithdrawalRequest::find($withdrawals);
        if (!$partnerWithdrawalRequest->isUpdateableByPartner($partner)) return api_response($request, '', 403, ['result' => 'You can not update this withdraw request']);

        $withdrawal_update = $partnerWithdrawalRequest->update([
            'status'          => 'cancelled',
            'updated_by'      => $request->manager_resource->id,
            'updated_by_name' => 'Resource - ' . $request->manager_resource->profile->name
        ]);
        return api_response($request, $withdrawal_update, 200);
    }

    private function calculateWithdrawableAmount(Partner $partner)
    {
        $total_pending_amount           =  $partner->withdrawalRequests()->active()->sum('amount') ? : 0;
        return ($withdrawable_amount = ($partner->wallet - ($total_pending_amount + ($partner->walletSetting->security_money ? floatval($partner->walletSetting->security_money) : 0)))) > 0 ? $withdrawable_amount : 0;
    }

    private function getBankInformation(Partner $partner)
    {
        $info = $partner->withdrawalBankInformations->first();
        if (!$info) return null;

        return [
            'bank_name' => $info->bank_name,
            'account_name' => $info->acc_name,
            'account_no' => $info->acc_no,
            'account_type' => $info->acc_type,
            'branch_name' =>  $info->branch_name,
            'routing_no' =>  $info->routing_no,
            'cheque_book_receipt' => $info->cheque_book_receipt,
        ];
    }

    public function getBankInfo(Request $request, $partner): JsonResponse
    {
        try {
            $bank_info = $this->getBankInformation($request->partner);
            return api_response($request, null, 200, ['data' => ['bank_info' => $bank_info]]);
        } catch (Throwable $exception) {
            logError($exception);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $partner
     * @return JsonResponse
     */
    public function updateBankInfo(Request $request, $partner): JsonResponse
    {
        try {
            $this->validate($request, [
                'bank_name' => 'required',
                'account_no' => 'required',
                'account_name' => 'required',
                'branch_name' => 'required',
                'cheque_book_receipt' => 'sometimes|required|file|mimes:jpg,jpeg,png',
            ]);

            $bank_information = Partner::find($partner)->withdrawalBankInformations->first();
            if(!$bank_information) {
                return api_response($request, null, 400, ["message" => "No bank information found for this partner."]);
            }

            if($request->hasFile('cheque_book_receipt')){
                if(isset($bank_information->cheque_book_receipt)) $this->deleteFile($bank_information->cheque_book_receipt);
                list($cheque_book_receipt, $cheque_book_receipt_filename) = $this->makeChequeBookReceipt($request->cheque_book_receipt, $request->partner->id.'cheque_book_receipt');
                $cheque_book_receipt = $this->saveImageToCDN($cheque_book_receipt, getPartnerChequeBookImageFolder(), $cheque_book_receipt_filename);
                $bank_information->cheque_book_receipt = $cheque_book_receipt;
            }
            $manager_resource = $request->manager_resource;
            $this->setModifier($manager_resource);

            $bank_information->partner_id  = $partner;
            $bank_information->bank_name = $request->bank_name;
            $bank_information->acc_no = $request->account_no;
            $bank_information->acc_name= $request->account_name;
            $bank_information->branch_name = $request->branch_name;
            $bank_information->routing_no = $request->routing_no;
            $bank_information->purpose = Purposes::PARTNER_WALLET_WITHDRAWAL;

            $this->withUpdateModificationField($bank_information);
            $bank_information->save();

            return api_response($request, null, 200,['message' => 'Bank Information updated successfully']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function storeBankInfo($partner, Request $request)
    {
        $this->validate($request, [
            'bank_name' => 'required',
            'account_no' => 'required',
            'account_name' => 'required',
            'branch_name' => 'required',
            'cheque_book_receipt' => 'sometimes|required|file|mimes:jpg,jpeg,png',

        ]);
        $cheque_book_receipt = null;
        if($request->hasFile('cheque_book_receipt')){
            list($cheque_book_receipt, $cheque_book_receipt_filename) = $this->makeChequeBookReceipt($request->cheque_book_receipt, $request->partner->id.'cheque_book_receipt');
            $cheque_book_receipt = $this->saveImageToCDN($cheque_book_receipt, getPartnerChequeBookImageFolder(), $cheque_book_receipt_filename);
        }
        $manager_resource = $request->manager_resource;
        $this->setModifier($manager_resource);

        $bank_information              = new PartnerBankInformation();
        $bank_information->partner_id  = $partner;
        $bank_information->bank_name = $request->bank_name;
        $bank_information->acc_no = $request->account_no;
        $bank_information->acc_name= $request->account_name;
        $bank_information->acc_type = null;
        $bank_information->branch_name = $request->branch_name;
        $bank_information->routing_no = $request->routing_no;
        $bank_information->cheque_book_receipt = $cheque_book_receipt;
        $bank_information->purpose = Purposes::PARTNER_WALLET_WITHDRAWAL;

        $this->withCreateModificationField($bank_information);
        $bank_information->save();
        return api_response($request, null, 200,['message' => 'Bank Information stored successfully']);
    }

    public function checkWithdrawRequestPendingStatus(Request $request)
    {
        $partner = $request->partner;

        $status_check = WithdrawalRequest::query()->whereIn('status', ['pending', 'approval_pending'])->where('payment_method', 'bkash')->where('requester_id', $partner->id)->first();

        if ($status_check  && $status_check->payment_method == 'bkash') {
            $message = 'ইতিমধ্যে আপনার ১ টি বিকাশের মাধ্যমে টাকা উত্তোলনের আবেদন প্রক্রিয়াধীন রয়েছে, অনুগ্রহ করে আবেদনটি সম্পূর্ণ হওয়া পর্যন্ত অপেক্ষা করুন অথবা ব্যাংকের মাধ্যমে টাকা উত্তোলনের আবেদন করুন।';
            return api_response($request, null, 200, [
                'data' => [
                    'bkash_pending_status' => true,
                    'message' => $message,
                    'current_balance' => $partner->wallet
                ]
            ]);
        }

        return api_response($request, null, 200, [
            'data' => [
                'bkash_pending_status' => false,
                'message' => 'বিকাশের মাধ্যমে একই সাথে একের অধিক টাকা উত্তোলনের আবেদন করা যাবে না। একটি আবেদন সম্পন্ন হবার পর আপনি পরবর্তী আবেদন করতে পারবেন।',
                'current_balance' => $partner->wallet
            ]
        ]);
    }
}
