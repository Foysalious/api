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
            $partner_subscription_packages = PartnerSubscriptionPackage::validDiscount()->select('id', 'name', 'tagline', 'rules', 'usps', 'badge')->get();
            foreach ($partner_subscription_packages as $package) {
                $package['rules'] = $this->calculateDiscount(json_decode($package->rules, 1), $package);
                $package['is_subscribed'] = (int) ($partner->package_id == $package->id);
                $package['usps'] = $package->usps ? json_decode($package->usps) : [];
                array_forget($package,'discount');
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
                $partner->subscribe($request->package_id, $request->billing_cycle);
            }

            return api_response($request, null, 200);

        }  catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function calculateDiscount($rules, PartnerSubscriptionPackage $package)
    {
        $rules['fee']['monthly']['original_price'] = $rules['fee']['monthly']['value'];
        $rules['fee']['monthly']['discount'] = $this->discountPrice($package, 'monthly');
        $monthly_discounted_price = $rules['fee']['monthly']['original_price'] - $rules['fee']['monthly']['discount'];
        $rules['fee']['monthly']['discounted_price'] = $monthly_discounted_price > 0 ? $monthly_discounted_price : 0;

        $rules['fee']['yearly']['original_price'] = $rules['fee']['yearly']['value'];
        $rules['fee']['yearly']['discount'] = $this->discountPrice($package, 'yearly');
        $yearly_discounted_price = $rules['fee']['yearly']['original_price'] - $rules['fee']['yearly']['discount'];
        $rules['fee']['yearly']['discounted_price'] = $yearly_discounted_price > 0 ? $yearly_discounted_price : 0;

        array_forget($rules, ['fee.monthly.value', 'fee.yearly.value']);

        return $rules;
    }

    private function discountPrice(PartnerSubscriptionPackage $package, $billing_type)
    {
        if ($package->discount) {
            $partner_subcription_discount = $package->discount->filter(function($discount) use ($billing_type){
                return $discount->billing_type == $billing_type;
            })->first();

            if ($partner_subcription_discount) {
                if (!$partner_subcription_discount->is_percentage) return (float) $partner_subcription_discount->amount;
                else {
                    return (float) $package->originalPrice($billing_type) * $partner_subcription_discount->amount;
                }
            }
            return 0;
        }
        return 0;
    }
}
