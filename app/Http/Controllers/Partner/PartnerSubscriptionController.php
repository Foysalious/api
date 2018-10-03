<?php namespace App\Http\Controllers\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerSubscriptionUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\ModificationFields;

class PartnerSubscriptionController extends Controller
{
    use ModificationFields;

    public function index($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $partner_subscription_packages = PartnerSubscriptionPackage::validDiscounts()->select('id', 'name', 'name_bn', 'show_name', 'show_name_bn', 'tagline', 'tagline_bn', 'rules', 'usps', 'badge')->get();
            foreach ($partner_subscription_packages as $package) {
                $package['rules'] = $this->calculateDiscount(json_decode($package->rules, 1), $package);
                $package['is_subscribed'] = (int)($partner->package_id == $package->id);
                $package['subscription_type'] = ($partner->package_id == $package->id) ? $partner->billing_type : null;
                $package['usps'] = $package->usps ? json_decode($package->usps) : ['usp' => [], 'usp_bn' => []];
                removeRelationsAndFields($package);
            }
            $data = [
                'subscription_package' => $partner_subscription_packages,
                'monthly_tag' => 'বাৎসরিক প্ল্যানে ২০% সেভ করুন ',
                'yearly_tag' => 'বাৎসরিক প্ল্যানে ২০% সেভ করুন ',
                'billing_type' => $partner->billing_type
            ];
            return api_response($request, null, 200, $data);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'package_id' => 'required|numeric|exists:partner_subscription_packages,id',
                'billing_cycle' => 'required|string|in:monthly,yearly'
            ]);
            $request->partner->subscribe((int)$request->package_id, $request->billing_cycle);
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
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

    public function update(Request $request)
    {
        try {
            /** @var Partner $partner */
            $partner = $request->partner;
            $this->validate($request, [
                'package_id' => 'required|numeric|exists:partner_subscription_packages,id',
                'billing_type' => 'required|string|in:monthly,yearly'
            ]);
            if (((int)$request->package_id > (int)$partner->package_id) ||
                ((int)$request->package_id == (int)$partner->package_id && $request->billing_type != $partner->billing_type && $partner->billing_type == 'monthly')) {

                if ($partner->canRequestForSubscriptionUpdate()) {

                    $this->setModifier($request->manager_resource);
                    $update_request_data = $this->withCreateModificationField([
                        'partner_id' => $partner->id,
                        'old_package_id' => $partner->package_id,
                        'new_package_id' => $request->package_id,
                        'old_billing_type' => $partner->billing_type,
                        'new_billing_type' => $request->billing_type
                    ]);
                    PartnerSubscriptionUpdateRequest::create($update_request_data);

                    return api_response($request, 1, 200, ['message' => "Subscription Update Request Created Successfully"]);
                }
                return api_response($request, null, 403, ['message' => "You already have a pending request"]);
            } elseif (((int)$request->package_id == (int)$partner->package_id) && $request->billing_type == $partner->billing_type) {
                return api_response($request, null, 403, ['message' => "You can't select the same package"]);
            } else {
                return api_response($request, null, 403, ['message' => "You can't downgrade your subscription."]);
            }
        } catch (ValidationException $e) {
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
        $rules['fee']['monthly']['discount_note'] = $this->discountNote($package, 'monthly');

        $rules['fee']['yearly']['original_price'] = $rules['fee']['yearly']['value'];
        $rules['fee']['yearly']['discount'] = $this->discountPrice($package, 'yearly');
        $yearly_discounted_price = $rules['fee']['yearly']['original_price'] - $rules['fee']['yearly']['discount'];
        $rules['fee']['yearly']['discounted_price'] = $yearly_discounted_price > 0 ? $yearly_discounted_price : 0;
        $rules['fee']['yearly']['discount_note'] = $this->discountNote($package, 'yearly');

        array_forget($rules, ['fee.monthly.value', 'fee.yearly.value']);

        return $rules;
    }

    private function discountPrice(PartnerSubscriptionPackage $package, $billing_type)
    {
        if ($package->discounts->count()) {
            $partner_subcription_discount = $package->discounts->filter(function ($discount) use ($billing_type) {
                return $discount->billing_type == $billing_type;
            })->first();

            if ($partner_subcription_discount) {
                if (!$partner_subcription_discount->is_percentage) return (float)$partner_subcription_discount->amount;
                else {
                    return (float)$package->originalPrice($billing_type) * ($partner_subcription_discount->amount / 100);
                }
            }
            return 0;
        }
        return 0;
    }

    private function discountNote(PartnerSubscriptionPackage $package, $billing_type)
    {
        if ($package->discounts->count()) {
            $partner_subcription_discount = $package->discounts->filter(function ($discount) use ($billing_type) {
                return $discount->billing_type == $billing_type;
            })->first();
            $partner_subcription_discount_cycle = json_decode($partner_subcription_discount ? $partner_subcription_discount->applicable_billing_cycles : '[]');
            if (isset($partner_subcription_discount_cycle[0]) && $partner_subcription_discount_cycle[0] == 1) {
                $max_number = $this->hasSequence($partner_subcription_discount_cycle);
                return $max_number ? "First $max_number Billing Cycle" : $this->getOrdinalMessage($partner_subcription_discount_cycle);
            } elseif (count($partner_subcription_discount_cycle)) {
                return $this->getOrdinalMessage($partner_subcription_discount_cycle);
            }
            return '';
        }
        return '';
    }

    private function hasSequence($cycles)
    {
        $has_sequence = 1;
        foreach ($cycles as $key => $cycle) {
            if ($key != 0 && $cycles[$key] != $cycles[$key - 1] + 1) {
                $has_sequence = 0;
                break;
            }
        }
        return $has_sequence ? count($cycles) : $has_sequence; // if doesn't have sequence it will return 0, otherwise number of cycles
    }

    private function getOrdinalmessage($cycles)
    {
        $message = [];
        foreach ($cycles as $cycle) {
            $message[] = ordinal($cycle);
        }
        return implode(',', $message) . " Billing Cycle";
    }
}
