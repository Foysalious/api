<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Sheba\Partner\PackageFeatureCount;
use Illuminate\Validation\ValidationException;

class PartnerSubscriptionPackageFeatureCountController extends Controller
{
    private $packageFeatureCount;

    public function __construct(PackageFeatureCount $packageFeatureCount)
    {
        $this->packageFeatureCount = $packageFeatureCount;
    }

    /**
     * @param Request $request
     * @param $partner
     * @return JsonResponse
     */
    public function getCurrentCount(Request $request, $partner): JsonResponse
    {
        try {
            $this->validate($request, [
                'feature' => "required|string|in:" . implode(',', constants('INCREMENTING_FEATURE'))
            ]);

            $feature = $request->feature;
            $current_count = $this->currentCount($feature, $partner);

            $data = [
                'count' => $current_count
            ];
            return api_response($request, null, 200, ['data' => $data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        }
    }

    /**
     * @param Request $request
     * @param $partner
     * @return JsonResponse
     */
    public function increment(Request $request, $partner): JsonResponse
    {
        try {
            $this->validate($request, [
                'count' => "required|numeric|min:0",
                'feature' => "required|string|in:" . implode(',', constants('INCREMENTING_FEATURE'))
            ]);

            $feature = $request->feature;
            $count = $request->count;

            $current_count = $this->currentCount($feature, $partner);
            $updated_count = $current_count + $count;
            $this->countUpdate($feature, $updated_count, $partner);

            $message = ucfirst($feature) . ' count incremented successfully';
            return api_response($request, null, 200, ['message' => $message]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        }
    }

    /**
     * @param Request $request
     * @param $partner
     * @return JsonResponse
     */
    public function decrement(Request $request, $partner): JsonResponse
    {
        try {
            $this->validate($request, [
                'count' => "required|numeric|min:0",
                'feature' => "required|string|in:" . implode(',', constants('INCREMENTING_FEATURE'))
            ]);

            $feature = $request->feature;
            $count = $request->count;

            $current_count = $this->currentCount($feature, $partner);
            $updated_count = $current_count - $count;
            if ($updated_count < 0) {
                $message = 'You do not have enough ' . ucfirst($feature);
                return api_response($request, null, 400, ['message' => $message]);
            }

            $this->countUpdate($feature, $updated_count, $partner);

            $message = ucfirst($feature) . ' count decremented successfully';
            return api_response($request, null, 200, ['message' => $message]);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        }
    }

    /**
     * @param $feature
     * @param $partner
     * @return mixed
     */
    private function currentCount($feature, $partner)
    {
        return $this->packageFeatureCount->featureCurrentCount($feature, $partner);
    }

    /**
     * @param $feature
     * @param $updated_count
     * @param $partner
     * @return mixed
     */
    private function countUpdate($feature, $updated_count, $partner)
    {
        return $this->packageFeatureCount->featureCountUpdate($feature, $updated_count, $partner);
    }
}