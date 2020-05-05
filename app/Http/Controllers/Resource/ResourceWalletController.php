<?php namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;

class ResourceWalletController extends Controller
{
    public function getWallet(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $wallet = [
            'balance' => $resource->totalWalletAmount(),
            'max_withdrawal_limit' => 600,
            'tag' => 'approval_pending',
            'message' => 'আপনার সর্বশেষ ব্যালেন্স উত্তোলনের রিকুয়েস্ট এখনো অপেক্ষমাণ আছে, তাই আপনি এখন রিকুয়েস্ট করতে পারবেন না।'
        ];
        return api_response($request, $wallet, 200, ['wallet' => $wallet]);
    }
}