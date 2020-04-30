<?php namespace App\Http\Controllers\Resource;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;

class ResourceTransactionController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'year' => 'sometimes|required|numeric',
            'month' => 'sometimes|required|numeric|min:1|max:12'
        ]);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        list($offset, $limit) = calculatePagination($request);
        $transactions = $resource->transactions()->select('id', 'type', 'amount', 'log', 'created_at')->orderBy('created_at', 'desc')->get();
        if ($request->has('month') && $request->has('year')) {
            $transactions = $transactions->filter(function ($transaction) use ($request) {
                $created_at = Carbon::parse($transaction['created_at']);
                return ($created_at->month == $request->month && $created_at->year == $request->year);
            });
        }
        $transactions = $transactions->splice($offset, $limit);
        return api_response($request, $transactions, 200, ['transactions' => $transactions]);
    }
}