<?php


namespace App\Http\Controllers;


use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    private $reviewRepository;

    public function __construct()
    {
        $this->reviewRepository = new ReviewRepository();
    }

    public function show($resource, Request $request)
    {
        try {
            $resource = $request->resource;
            $profile = $resource->profile;
            $resource['name'] = $profile->name;
            $resource['mobile'] = $profile->mobile;
            $resource['address'] = $profile->address;
            removeRelationsFromModel($resource);
            return api_response($request, $resource, 200, ['resource' => removeSelectedFieldsFromModel($resource)]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getReviews($resource, Request $request)
    {
        try {
            $resource = $request->resource;
            $resource->load(['reviews' => function ($q) {
                $q->with('job.partner_order.order');
            }]);
            $breakdown = $this->reviewRepository->getReviewBreakdown($resource->reviews);
            $resource = $this->reviewRepository->getGeneralReviewInformation($resource);
            $resource = $this->reviewRepository->filterReviews($resource);
            foreach ($resource->reviews as $review) {
                $review['order_id'] = $review->job->partner_order->order->id;
                $review['order_code'] = $review->job->partner_order->order->code();
                removeRelationsFromModel($review);
                removeSelectedFieldsFromModel($review);
            }
            return api_response($request, $resource, 200, ['reviews' => $resource->reviews, 'breakdown' => $breakdown]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}