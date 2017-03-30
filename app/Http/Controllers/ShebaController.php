<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Resource;
use App\Models\Service;
use App\Models\Slider;
use Illuminate\Http\Request;

use App\Http\Requests;

class ShebaController extends Controller
{
    public function getInfo()
    {
//        $customer_count = Customer::all()->count() + 3000;
//        $partner_count = Partner::all()->count();
//        $job_count = Job::all()->count() + 16000;
        $job_count = Job::where('status', 'Served')->count() + 16000;
        $service_count = Service::where('publication_status', 1)->get()->count();
        $resource_count = Resource::whereHas('partners', function ($query) {
            $query->where([
                ['resource_type', 'Handyman'],
                ['is_verified', 1]
            ]);
        })->get()->count();
        $images = $this->getImages();
        return response()->json(['service' => $service_count, 'job' => $job_count,
            'resource' => $resource_count, 'images' => $images, 'msg' => 'successful', 'code' => 200]);
    }

    public function getImages()
    {
        $images = Slider::select('id', 'image_link', 'target_link')->show();
        return $images;
    }
}
