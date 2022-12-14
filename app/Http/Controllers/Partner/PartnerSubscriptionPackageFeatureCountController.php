<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Subscription\Partner\PartnerSubscription;

class PartnerSubscriptionPackageFeatureCountController extends Controller
{
    private $partnerSubscription;

    public function __construct(PartnerSubscription $partnerSubscription)
    {
        $this->partnerSubscription = $partnerSubscription;
    }

    /**
     * @param Request $request
     * @param $partner
     * @return JsonResponse
     */
    public function getFeaturesCurrentCount(Request $request, $partner): JsonResponse
    {
        $features_count = $this->partnerSubscription->packageFeatureCount($partner);
        if (! $features_count) {
            return api_response($request, null, 404, ['message' => 'Partner not found']);
        }

        return api_response($request, null, 200, ['data' => $features_count]);
    }

//    /**
//     * @param Request $request
//     * @param $partner
//     * @return JsonResponse
//     */
//    public function increment(Request $request, $partner): JsonResponse
//    {
//        try {
//            $this->validate($request, [
//                'count' => "required|numeric|min:0",
//                'feature' => "required|string|in:" . implode(',', constants('INCREMENTING_FEATURE'))
//            ]);
//
//            $feature = $request->feature;
//            $count = $request->count;
//
//            $current_count = $this->currentCount($feature, $partner);
//            $updated_count = $current_count + $count;
//            $this->countUpdate($feature, $updated_count, $partner);
//
//            $message = ucfirst($feature) . ' count incremented successfully';
//            return api_response($request, null, 200, ['message' => $message]);
//        } catch (ValidationException $e) {
//            $message = getValidationErrorMessage($e->validator->errors()->all());
//            return api_response($request, $message, 400, ['message' => $message]);
//        }
//    }
//
//    /**
//     * @param Request $request
//     * @param $partner
//     * @return JsonResponse
//     */
//    public function decrement(Request $request, $partner): JsonResponse
//    {
//        try {
//            $this->validate($request, [
//                'count' => "required|numeric|min:0",
//                'feature' => "required|string|in:" . implode(',', constants('INCREMENTING_FEATURE'))
//            ]);
//
//            $feature = $request->feature;
//            $count = $request->count;
//
//            $current_count = $this->currentCount($feature, $partner);
//            $updated_count = $current_count - $count;
//            if ($updated_count < 0) {
//                $message = 'You do not have enough ' . ucfirst($feature);
//                return api_response($request, null, 400, ['message' => $message]);
//            }
//
//            $this->countUpdate($feature, $updated_count, $partner);
//
//            $message = ucfirst($feature) . ' count decremented successfully';
//            return api_response($request, null, 200, ['message' => $message]);
//
//        } catch (ValidationException $e) {
//            $message = getValidationErrorMessage($e->validator->errors()->all());
//            return api_response($request, $message, 400, ['message' => $message]);
//        }
//    }

//    /**
//     * @param $feature
//     * @param $updated_count
//     * @param $partner
//     * @return mixed
//     */
//    private function countUpdate($feature, $updated_count, $partner)
//    {
//        return $this->packageFeatureCount->featureCountUpdate($feature, $updated_count, $partner);
//    }
}