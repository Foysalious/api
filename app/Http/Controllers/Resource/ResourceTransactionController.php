<?php namespace App\Http\Controllers\Resource;

use Dingo\Blueprint\Annotation\Resource;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Dal\ResourceTransaction\Model as ResourceTransaction;

class ResourceTransactionController extends Controller
{
    public function index(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        list($offset, $limit) = calculatePagination($request);
        $transactions = ResourceTransaction::where('resource_id', $resource->id)->select('id', 'type', 'amount', 'log', 'created_at')->get()->splice($offset, $limit);
        return api_response($request, $transactions, 200, ['transactions' => $transactions]);
    }

    public function getWallet(Request $request)
    {
        $wallet = [
            'balance' => 1000,
            'max_withdrawal_limit' => 600,
            'tag' => 'request_accepted',
            'message' => 'আপনার সর্বশেষ টাকা উত্তোলনের রিকুয়েস্টটি অনুমোদন করা হয়নি। ২৪ ঘণ্টার মধ্যে নিম্নে দেয়া বিকাশ নাম্বারে টাকা রিচার্জ হয়ে যাবে'
        ];
        return api_response($request, $wallet, 200, ['wallet' => $wallet]);
    }
}