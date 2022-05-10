<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
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

    public function increment(Request $request, $partner)
    {
        try {
            $this->validate($request, [
                'count' => "required|numeric|min:0",
                'feature' => "required|in:" . implode(',', constants('INCREMENT_FEATURE'))
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

    public function decrement(Request $request, $partner)
    {
        $feature = $request->feature;
        $count = $request->count;

        $current_count = $this->currentCount($feature, $partner);
    }

    private function currentCount($feature, $partner)
    {
        $methodName = $feature . 'CurrentCount';
        return $this->packageFeatureCount->$methodName($partner);
    }

    private function countUpdate($feature, $updated_count, $partner)
    {
        $methodName = $feature . 'CountUpdate';
        return $this->packageFeatureCount->$methodName($updated_count, $partner);
    }
}