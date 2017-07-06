<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\OfferShowcase;
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
        $job_count = Job::all()->count() + 16000;
        $service_count = Service::where('publication_status', 1)->get()->count();
        $resource_count = Resource::whereHas('partners', function ($q) {
            $q->where([
                ['resource_type', 'Handyman'],
                ['is_verified', 1]
            ]);
        })->get()->count();
//        $images = $this->getImages();
        return response()->json(['service' => $service_count, 'job' => $job_count,
            'resource' => $resource_count,
//            'images' => $images,
            'msg' => 'successful', 'code' => 200]);
    }

    public function getImages()
    {
        $images = Slider::select('id', 'image_link', 'target_link')->show();
        return response()->json(['images' => $images, 'msg' => 'successful', 'code' => 200]);
    }

    public function getOffers()
    {
        $offers = OfferShowcase::select('id', 'thumb', 'title', 'short_description', 'target_link')
            ->where('is_active', 1)->get();
        return response()->json(['offers' => $offers, 'code' => 200]);
    }

    public function getOffer($offer)
    {
        $offer = OfferShowcase::select('id', 'thumb', 'title', 'banner', 'short_description', 'detail_description', 'target_link')
            ->where([
                ['id', $offer],
                ['is_active', 1]
            ])->first();
        return count($offer) > 0 ? response()->json(['offer' => $offer, 'code' => 200]) : response()->json(['code' => 404]);
    }

    public function getSimilarOffer($offer)
    {
        $offer = OfferShowcase::select('id', 'thumb', 'title', 'banner', 'short_description', 'detail_description', 'target_link')
            ->where([
                ['id', '<>', $offer],
                ['is_active', 1]
            ])->get();
        return count($offer) >= 3 ? response()->json(['offer' => $offer, 'code' => 200]) : response()->json(['code' => 404]);
    }
}
