<?php namespace App\Http\Controllers;

use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\ProfileRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;

class ResourceController extends Controller
{
    private $reviewRepository;
    private $profileRepo;

    const REPTO_IP = '52.89.162.43';

    #const REPTO_IP = '103.4.146.66';

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
            $resource['ongoing_jobs'] = $resource->jobs->whereIn('status', ['Schedule Due', 'Process', 'Accepted', 'Serve Due'])->count();
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
            $info = array('rating' => $resource['rating'], 'total_reviews' => $reviews->count(), 'reviews' => array_slice($reviews->toArray(), $offset, $limit), 'breakdown' => $breakdown);
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getResourceData(Request $request)
    {
        try {
            $mobile = formatMobile($request->mobile);
            if ($profile = $this->profileRepo->getIfExist($mobile, 'mobile')) {
                if ($profile->resource) return api_response($request, null, 400, ['message' => 'Resource already Exist']);
                return api_response($request, null, 200, ['profile' => collect($profile)->only(['id', 'name', 'mobile', 'address', 'pro_pic', 'email'])]);
            }
            return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function trainingStatusUpdate(Request $request)
    {
        try {
            $this->validate($request, [
                'mobile' => 'required',
                'is_trained' => 'boolean',
                'certificates' => 'required|array'
            ]);

            if ($request->ip() != self::REPTO_IP) {
                $message = 'Your IP Is Incorrect';
                return api_response($request, $message, 500, ['message' => $message]);
            }

            $profile = Profile::where('mobile', $request->mobile)->first();
            $profile->resource->update(['is_trained' => count($request->certificates) > 0 ? 1 : 0]);

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}