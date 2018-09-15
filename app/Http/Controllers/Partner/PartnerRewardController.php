<?php

namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PartnerRewardController extends Controller
{
    public function index(Request $request)
    {
        try {

        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}