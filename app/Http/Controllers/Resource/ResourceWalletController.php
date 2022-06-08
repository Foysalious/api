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

        if (!$resource->is_verified) {
            $status['tag'] = 'not_verified';
            $status['message'] = 'আপনি বর্তমানে আনভেরিফায়েড অবস্থায় আছেন, তাই আপনি এখন রিকুয়েস্ট করতে পারবেন না বিস্তারিত জানতে ১৬৫১৬ নম্বরে যোগাযোগ করুন।';
        } elseif ($resource->totalWalletAmount() <= 0) {
            $status['tag'] = 'not_enough_balance';
            $status['message'] = 'আপনার অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই, তাই আপনি এখন রিকুয়েস্ট করতে পারবেন না।';
        } elseif ($last_withdrawal_request) {
            $status = $message->setResource($resource)->setStatus($last_withdrawal_request->status)->getLatestWithdrawalRequestUpdate();
        } else {
            $status['tag'] = null;
            $status['message'] = null;
        }
        $wallet = [
            'balance' => $resource->totalWalletAmount(),
            'min_withdrawal_limit' => constants('WITHDRAW_LIMIT')['bkash']['min'],
            'max_withdrawal_limit' => constants('WITHDRAW_LIMIT')['bkash']['max'],
            'tag' => $status['tag'],
            'message' => $status['message']
        ];
        return api_response($request, $wallet, 200, ['wallet' => $wallet]);
    }
}