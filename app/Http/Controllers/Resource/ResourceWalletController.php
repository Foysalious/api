<?php namespace App\Http\Controllers\Resource;

use App\Sheba\Resource\WithdrawalRequest\LatestWithdrawalRequestUpdate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;

class ResourceWalletController extends Controller
{
    public function getWallet(Request $request, LatestWithdrawalRequestUpdate $message)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $last_withdrawal_request = $resource->withdrawalRequests()->latest()->first();
        if ($last_withdrawal_request) {
            $status = $message->setResource($resource)->setStatus($last_withdrawal_request->status)->getLatestWithdrawalRequestUpdate();
        } else {
            $status['tag'] = null;
            $status['message'] = null;
        }
        $wallet = [
            'balance' => $resource->totalWalletAmount(),
            'max_withdrawal_limit' => config('sheba.resource_max_withdraw_limit'),
            'tag' => $status['tag'],
            'message' => $status['message']
        ];
        return api_response($request, $wallet, 200, ['wallet' => $wallet]);
    }
}