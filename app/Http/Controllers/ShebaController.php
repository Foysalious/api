<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\PartnerServiceDiscount;
use App\Models\Resource;
use App\Models\Service;
use App\Models\Slider;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;

class ShebaController extends Controller
{
    private $serviceRepository;
    private $reviewRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
        $this->reviewRepository = new ReviewRepository();
    }

    public function getInfo()
    {
//        $customer_count = Customer::all()->count() + 3000;
//        $partner_count = Partner::all()->count();
//        $job_count = Job::all()->count() + 16000;
        $job_count = Job::where('status', 'Served')->count() + 16000;
        $service_count = Service::where('publication_status', 1)->get()->count();
        $resource_count = Resource::whereHas('partners', function ($q) {
            $q->where([
                ['resource_type', 'Handyman'],
                ['is_verified', 1]
            ]);
        })->get()->count();
        $images = $this->getImages();
        return response()->json(['service' => $service_count, 'job' => $job_count,
            'resource' => $resource_count, 'images' => $images, 'msg' => 'successful', 'code' => 200]);
    }

    private function getImages()
    {
        $images = Slider::select('id', 'image_link', 'target_link')->show();
        return $images;
    }

    public function getOffers()
    {
        $now = Carbon::now();
        $discounts = PartnerServiceDiscount::with(['partnerService' => function ($q) {
            $q->select('id', 'partner_id', 'service_id')
                ->with(['service' => function ($q) {
                    $q->select('id', 'name', 'banner', 'variable_type');
                }]);
        }])->select('id', 'partner_service_id', 'amount')->where(function ($q) use ($now) {
            $q->where('start_date', '<=', $now);
            $q->where('end_date', '>=', $now);
        })->get();
        foreach ($discounts as $discount) {
            $discount->partnerService->service = $this->serviceRepository->getStartEndPrice($discount->partnerService->service);
            $this->reviewRepository->getReviews($discount->partnerService->service);
            array_add($discount->partnerService->service, 'discount', $discount->amount);
            array_add($discount, 'service', $discount->partnerService->service);
            array_forget($discount, 'partnerService');
            array_forget($discount, 'amount');
            array_forget($discount, 'partner_service_id');
        }
        return response()->json(['offers' => $discounts]);
    }
}
