<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function insertLocation(Request $request)
    {
        $req = $request->except('access_token', 'auth_user', 'auth_info', 'manager_member', 'business', 'business_member', 'token', 'profile');

        return api_response($request, null, 200, ['data' => $req]);
    }
}