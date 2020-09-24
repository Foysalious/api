<?php namespace App\Http\Controllers\Resource;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\Transaction\TransactionList;

class ResourceTransactionController extends Controller
{
    public function index(Request $request, TransactionList $transactionList)
    {
        $this->validate($request, [
            'year' => 'sometimes|required|numeric',
            'month' => 'sometimes|required|numeric|min:1|max:12'
        ]);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        list($offset, $limit) = calculatePagination($request);
        $transactions = $transactionList->setResource($resource)
            ->setMonth($request->month)
            ->setYear($request->year)
            ->setOffset($offset)
            ->setLimit($limit)
            ->get();
        return api_response($request, $transactions, 200, ['transactions' => $transactions]);
    }
}