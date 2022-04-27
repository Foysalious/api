<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Sheba\Partner\PackageFeatureCount;

class PartnerSubscriptionPackageFeatureCountController extends Controller
{
    private $packageFeatureCount;

    public function __construct(PackageFeatureCount $packageFeatureCount)
    {
        $this->packageFeatureCount = $packageFeatureCount;
    }

    public function increment(Request $request)
    {
        $feature = $request->feature;
        $count = $request->count;

        $current_count = $this->currentCount($feature);
        dd($current_count);
    }

    public function decrement($feature, $count)
    {

    }

    private function currentCount($feature)
    {
        $methodName = $feature . 'CurrentCount';
        return $this->packageFeatureCount->$methodName();
    }
}