<?php


namespace App\Http\Controllers;


use App\Models\PartnerResource;
use App\Repositories\ProfileRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    private $reviewRepository;
    private $profileRepo;

    public function __construct()
    {
        $this->reviewRepository = new ReviewRepository();
        $this->profileRepo = new ProfileRepository();
    }

    public function show($partner, $resource, Request $request)
    {
        try {
            $resource = $request->resource;
            $specialized_categories = $resource->categoriesIn($request->partner->id)->pluck('name');
            $resource['specialized_categories'] = $specialized_categories;
            $resource['total_specialized_categories'] = $specialized_categories->count();
            $resource['served_jobs'] = $resource->jobs->where('status', 'Served')->count();
            $resource['ongoing_jobs'] = $resource->jobs->whereIn('status', ['Schedule Due', 'Process', 'Accepted','Serve Due'])->count();
            $profile = $resource->profile;
            $resource['name'] = $profile->name;
            $resource['mobile'] = $profile->mobile;
            $resource['address'] = $profile->address;
            $resource['profile_picture'] = $profile->pro_pic;
            $resource['rating'] = $this->reviewRepository->getAvgRating($resource->reviews);
            $resource['total_rating'] = $resource->reviews->count();
            $resource['total_reviews'] = $resource->reviews->filter(function ($item, $key) {
                return $item->review != '' || $item->review != null;
            })->count();
            $resource['joined_at'] = (PartnerResource::where([['resource_id', $resource->id], ['partner_id', (int)$partner]])->first())->created_at->timestamp;
            removeRelationsAndFields($resource);
            return api_response($request, $resource, 200, ['resource' => $resource]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getReviews($resource, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $resource = $request->resource->load(['reviews' => function ($q) {
                $q->with('job.partner_order.order');
            }]);
            $breakdown = $this->reviewRepository->getReviewBreakdown($resource->reviews);
            $resource['rating'] = $this->reviewRepository->getAvgRating($resource->reviews);
            $resource['total_rating'] = $resource->reviews->count();
            $reviews = $resource->reviews->filter(function ($item, $key) {
                return $item->review != '' || $item->review != null;
            })->sortByDesc('created_at');
            $resource['total_reviews'] = $reviews->count();
            foreach ($reviews as $review) {
                $review['order_id'] = $review->job->partner_order->id;
                $review['order_code'] = $review->job->partner_order->code();
                removeRelationsAndFields($review);
            }
            $info = array(
                'rating' => $resource['rating'],
                'total_reviews' => $reviews->count(),
                'reviews' => array_slice($reviews->toArray(), $offset, $limit),
                'breakdown' => $breakdown
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getResourceData(Request $request)
    {
        try{
            if ($request->has('mobile')) {
                $profile = $this->profileRepo->getIfExist(formatMobile(trim($request->mobile)), 'mobile');
                if ($profile) {
                    if ($profile->resource) return api_response($request, null, 400, ['message' => 'Resource Already Exist']);
                    return api_response($request, null, 200, ['profile' => $profile]);
                }
                return api_response($request, null, 404);
            } else return api_response($request, null, 400, ['message' => 'Mobile field required']);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}