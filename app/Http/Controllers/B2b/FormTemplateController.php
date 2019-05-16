<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class FormTemplateController extends Controller
{

    public function store(Request $request)
    {
        return api_response($request, null, 200, ['id' => 1]);
    }
}