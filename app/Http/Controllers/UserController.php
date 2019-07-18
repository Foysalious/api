<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;

class UserController extends Controller
{

    public function show(Request $request)
    {
        try {
            $user = $request->user;
            $data = [
                'name' => $user->name,
            ];
//            return api_response($request,200,)
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}