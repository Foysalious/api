<?php namespace App\Http\Controllers\Resource;

use App\Models\WithdrawalRequest;
use App\Sheba\Resource\WithdrawalRequest\WithdrawalRequestDenialMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;

class ResourceWalletController extends Controller
{
    public function getWallet(Request $request, WithdrawalRequestDenialMessage $message)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $last_withdrawal_request_status = $resource->withdrawalRequests()->latest()->first()->status;
        $status = $message->setResource($resource)->setStatus($last_withdrawal_request_status)->getWithdrawalRequestDenialMessage();
        $wallet = [
            'balance' => $resource->totalWalletAmount(),
            'max_withdrawal_limit' => config('sheba.resource_max_withdraw_limit'),
            'tag' => $status['tag'],
            'message' => $status['message']
        ];
        return api_response($request, $wallet, 200, ['wallet' => $wallet]);
    }
}