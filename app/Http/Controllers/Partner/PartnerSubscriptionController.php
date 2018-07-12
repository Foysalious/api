<?php namespace App\Http\Controllers\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class PartnerSubscriptionController extends Controller
{
    public function index(Partner $partner, Request $request)
    {
        try {
            $partner_subscription_packages = PartnerSubscriptionPackage::select('id', 'name', 'tagline', 'rules', 'usps', 'badge')->get();

            foreach ($partner_subscription_packages as $package) {
                $package['is_subscribed'] = (int) ($partner->package_id == $package->id);
            }
            return api_response($request, null, 200, ['subscription_package' => $partner_subscription_packages]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
