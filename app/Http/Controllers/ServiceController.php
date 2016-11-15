<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller {
    public function show($service,$name)
    {
        $service=Service::where('id',$service)
            ->select('name','description','recurring_possibility','thumb','banner','faqs')->get();
    }
}
