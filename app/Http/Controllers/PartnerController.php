<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class PartnerController extends Controller
{
    private $serviceRepository;
    private $reviewRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
        $this->reviewRepository = new ReviewRepository();
    }

    public function index()
    {
        $partners = Partner::select('id', 'name', 'sub_domain', 'logo')->where('status', 'Verified')->orderBy('name')->get();
        return response()->json(['partners' => $partners, 'code' => 200, 'msg' => 'successful']);
    }

    public function getPartnerServices($partner, Request $request)
    {
        $location = $request->has('location') ? $request->location : 4;
        $partner = Partner::select('id', 'name', 'sub_domain', 'description', 'logo', 'type', 'level')
            ->where('sub_domain', $partner)
            ->first();
        if ($partner == null) {
            return response()->json(['msg' => 'not found', 'code' => 404]);
        }
        $review = $partner->reviews()->where('review', '<>', '')->count('review');
        $rating = round($partner->reviews()->avg('rating'), 1);
        if ($rating == 0) {
            $rating = 5;
        }
        $served_job_count = $partner->jobs()->where('status', 'Served')->count();
        $resource_count = $partner->resources()->where('resources.is_verified', 1)->count();

        array_add($partner, 'review', $review);
        array_add($partner, 'rating', $rating);
        array_add($partner, 'job_count', $served_job_count);
        array_add($partner, 'resource_count', $resource_count);

        $partner_services = $partner->services()
            ->select('services.id', 'services.banner', 'services.category_id', 'services.publication_status', 'name', 'variable_type', 'services.min_quantity')
            ->where([
                ['is_verified', 1],
                ['is_published', 1],
                ['services.publication_status', 1]
            ])->get();
        $count_of_partner_services = count($partner_services);
        array_add($partner, 'service_count', $count_of_partner_services);
        if ($count_of_partner_services > 6) {
            $partner_services = $partner_services->random(6);
        }
        $final_service = [];
        foreach ($partner_services as $service) {
            $service = $this->serviceRepository->getStartPrice($service, $location);
            array_add($service, 'slug_service', str_slug($service->name, '-'));
            //review count of partner of this service
            $review = $service->reviews()->where([
                ['review', '<>', ''],
                ['partner_id', $partner->id]
            ])->count('review');
            //avg rating of the partner for this service
            $rating = $service->reviews()->where('partner_id', $partner->id)->avg('rating');
            array_add($service, 'review', $review);
            if ($rating == null) {
                array_add($service, 'rating', 5);
            } else {
                array_add($service, 'rating', round($rating, 1));
            }
            array_forget($service, 'pivot');
            array_push($final_service, $service);
        }
        if (count($partner) > 0) {
            return response()->json([
                'partner' => $partner,
                'services' => $final_service,
                'msg' => 'successful',
                'code' => 200
            ]);
        }
    }

    public function getReviews($partner)
    {
        $partner = Partner::with(['reviews' => function ($q) {
            $q->select('id', 'service_id', 'partner_id', 'customer_id', 'review_title', 'review', 'rating', DB::raw('DATE_FORMAT(updated_at, "%M %d,%Y at %h:%i:%s %p") as time'))
                ->with(['service' => function ($q) {
                    $q->select('id', 'name');
                }])->with(['customer' => function ($q) {
                    $q->select('id', 'profile_id')->with(['profile' => function ($q) {
                        $q->select('id', 'name');
                    }]);
                }])->orderBy('updated_at', 'desc');
        }])->select('id')->where('id', $partner)->first();
        if (count($partner->reviews) > 0) {
            $partner = $this->reviewRepository->getGeneralReviewInformation($partner);
            $breakdown = $this->reviewRepository->getReviewBreakdown($partner->reviews);
            $reviews = $partner->reviews->filter(function ($review, $key) {
                return $review->review != '' || $review->review != null;
            })->values()->all();
            array_forget($partner, 'reviews');
            $partner['reviews'] = $reviews;
            return response()->json(['msg' => 'ok', 'code' => 200, 'partner' => $partner, 'breakdown' => $breakdown]);
        }
        return response()->json(['msg' => 'not found', 'code' => 404]);
    }
}
