<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Http\Requests\InfoCallCreateRequest;

class InfoCallController extends Controller
{
    public function index()
    {

    }

    public function store(InfoCallCreateRequest $request)
    {
        dd($request->service_name);
    }

    public function show($id)
    {

    }
}