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
                $package['rules'] = json_decode($package->rules, 1);
                $package['is_subscribed'] = (int) ($partner->package_id == $package->id);
                $package['usps'] = $package->usps ? json_decode($package->usps) : [];
            }
            return api_response($request, null, 200, ['subscription_package' => $partner_subscription_packages]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Partner $partner, Request $request)
    {
        try{
            $this->validate($request, [
                'package_id'    => 'required|exists:partner_subscription_packages,id',
                'billing_cycle' => 'required|in:monthly,yearly'
            ]);

            if (is_null($partner->package_id)) {
                $partner->update([
                    'package_id' => $request->package_id,
                    'billing_cycle' => $request->billing_cycle
                ]);
            }

            return api_response($request, null, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        }
    }
}
