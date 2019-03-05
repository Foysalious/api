<?php
namespace App\Http\Controllers;

use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\ProfileRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;

class SpLoanController extends Controller
{
    public function getPersonalInformation($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;

            #dd($partner, $manager_resource, $profile, $basic_informations);
            $info = array(
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'gender' => $profile->gender,
                'picture' => $profile->pro_pic,
                'birthday' => $profile->dob,
                'present_address' => $profile->address,
                'permanent_address' => "Change",
                'father_name' => $profile->father_name,
                'spouse_name' => $profile->spouse_name,
                'husband_name' => "Change",
                'profession' => $profile->profession,
                'expenses' => [
                    'family_cost_per_month' => "Change",
                    'cost_per_month' => "Change",
                    'total_asset' => "Change",
                    'other_loan_installments_per_month' => "Change",
                    'utility_bill' => "Change"
                ]
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

   /* public function getReviews($resource, Request $request)
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
    }*/

}