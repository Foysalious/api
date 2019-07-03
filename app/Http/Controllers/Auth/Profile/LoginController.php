<?php namespace App\Http\Controllers\Auth\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        return api_response($request, true, 200, ['data' => [
            'token' => str_random(30),
            'has_partner' => $request->has_partner ? (int)$request->has_partner : 0,
            'has_resource' => $request->has_resource ? (int)$request->has_resource : 0,
        ]]);
    }
}