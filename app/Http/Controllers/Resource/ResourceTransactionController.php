<?php namespace App\Http\Controllers\Resource;

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
}