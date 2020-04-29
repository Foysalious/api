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
            'tag' => 'approval_pending',
            'message' => 'আপনার সর্বশেষ ব্যালেন্স উত্তোলনের রিকুয়েস্ট এখনো অপেক্ষমাণ আছে, তাই আপনি এখন রিকুয়েস্ট করতে পারবেন না।'
        ];
        return api_response($request, $wallet, 200, ['wallet' => $wallet]);
    }
}