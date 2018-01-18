<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class CustomerFavoriteController extends Controller
{
    public function store($customer, Request $request)
    {
        $customer=$request->customer;
    }
}