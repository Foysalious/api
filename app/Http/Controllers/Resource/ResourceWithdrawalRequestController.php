<?php namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ResourceWithdrawalRequestController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric',
            'bkash_number' => 'required|mobile:bd',
        ]);
        return api_response($request, null, 200);
    }
}