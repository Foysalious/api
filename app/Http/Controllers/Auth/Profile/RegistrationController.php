<?php namespace App\Http\Controllers\Auth\Profile;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{

    public function register(Request $request)
    {
        return api_response($request, true, 200, ['data' => [
            'token' => str_random(30),
        ]]);
    }
}