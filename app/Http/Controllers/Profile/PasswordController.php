<?php namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordController extends Controller
{

    public function store(Request $request)
    {
        return api_response($request, true, 200);
    }
}