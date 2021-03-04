<?php namespace App\Http\Controllers;

use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\ProfileRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Illuminate\Support\Facades\Redis;
use Sheba\Partner\LeaveStatus;
use Throwable;

class ResourceController extends Controller
{
    const COMPLIMENT_QUESTION_ID = 2;
    private $reviewRepository;
    private $profileRepo;

    const REPTO_IP = '52.89.162.43';
    #const REPTO_IP = '103.4.146.66';
    /** @var LeaveStatus */
    private $leaveStatus;

    public function __construct()
    {
        $this->reviewRepository = new ReviewRepository();
        $this->profileRepo = new ProfileRepository();
        $this->leaveStatus = new LeaveStatus();
    }

    /**
     * @param $partner
     * @param $resource
     * @param Request $request
     * @return JsonResponse
     */
    public function show($partner, $resource, Request $request)
    {
        try {
            $resource = $request->resource;
            $leave_status = $this->leaveStatus->setArtisan($resource)->getCurrentStatus();
            $specialized_categories = $resource->categoriesIn($request->partner->id)->pluck('name');
            $resource['specialized_categories'] = $specialized_categories;
            $resource['total_specialized_categories'] = $specialized_categories->count();
            $resource['served_jobs'] = $resource->jobs()->where('status', 'Served')->count();
            $resource['ongoing_jobs'] = $resource->jobs()->whereIn('status', ['Schedule Due', 'Process', 'Accepted', 'Serve Due'])->count();
            $profile = $resource->profile;
            $resource['name'] = $profile->name;
            $resource['mobile'] = $profile->mobile;
            $resource['email'] = $profile->email;
            $resource['dob'] = $profile->dob;
            $resource['address'] = $profile->address;
            $resource['profile_picture'] = $profile->pro_pic;
            $resource['rating'] = $this->reviewRepository->getAvgRating($resource->reviews);
            $resource['total_rating'] = $resource->reviews->count();
            $resource['total_reviews'] = $resource->reviews->filter(function ($item, $key) {
                return $item->review != '' || $item->review != null;
            })->count();
            $resource['joined_at'] = (PartnerResource::where([['resource_id', $resource->id], ['partner_id', (int)$partner]])->first())->created_at->timestamp;
            $resource['types'] = $resource->typeIn($partner);
            $resource['is_online'] = $leave_status['status'] ? 0 : 1;
            removeRelationsAndFields($resource);
            return api_response($request, $resource, 200, ['resource' => $resource]);
        } catch (Throwable $e) {
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
                $q->with([
                    'rates' => function ($q) {
                        $q->select('review_id', 'review_type', 'rate_answer_id')->where('rate_question_id', self::COMPLIMENT_QUESTION_ID)->with([
                            'answer' => function ($q) {
                                $q->select('id', 'answer', 'badge', 'asset');
                            }
                        ]);
                    }
                ]);
            }]);
            $breakdown = $this->reviewRepository->getReviewBreakdown($resource->reviews);
            $resource['rating'] = $this->reviewRepository->getAvgRating($resource->reviews);
            $resource['total_rating'] = $resource->reviews->count();
            $reviews = $resource->reviews->filter(function ($item, $key) {
                return $item->review != '' || $item->review != null;
            })->sortByDesc('created_at');
            $resource['total_reviews'] = $reviews->count();
            $compliment_counts = $resource->reviews->pluck('rates')->filter(function ($rate) {
                return $rate->count();
            })->flatten()->groupBy('rate_answer_id')->map(function ($answer, $index) {
                $first_answer = $answer->first();
                return [
                    'id' => $index,
                    'name' => $first_answer->answer->answer,
                    'badge' => $first_answer->answer->badge,
                    'asset' => $first_answer->answer->asset,
                    'count' => $answer->count(),
                ];
            });
            foreach ($reviews as $review) {
                $review['order_id'] = $review->job->partner_order->id;
                $review['order_code'] = $review->job->partner_order->code();
                removeRelationsAndFields($review);
            }
            $info = array('rating' => $resource['rating'], 'total_reviews' => $reviews->count(), 'reviews' => array_slice($reviews->toArray(), $offset, $limit), 'compliments' => $compliment_counts->values(), 'breakdown' => $breakdown);
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function trainingStatusUpdate(Request $request)
    {
        try {
            $repto_request_data = 'Repto:Request_'. $request->mobile;
            Redis::set($repto_request_data, json_encode($request->all()));
            $this->validate($request, [
                'mobile' => 'required',
                'is_trained' => 'boolean',
                'certificates' => 'required|array'
            ]);

            if ($request->ip() != self::REPTO_IP) {
                $message = 'Your IP Is Incorrect';
                return api_response($request, $message, 500, ['message' => $message]);
            }

            $profile = Profile::where('mobile', BDMobileFormatter::format($request->mobile))->first();
            $profile->resource->update(['is_trained' => count($request->certificates) > 0 ? 1 : 0]);

            return api_response($request, 1, 200, ['message' => 'Resource trained successfully']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}
