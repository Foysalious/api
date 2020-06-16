<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerSubscriptionUpdateRequest;
use App\Repositories\NotificationRepository;
use App\Sheba\Subscription\Partner\PartnerSubscriptionChange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\Partner\PartnerStatuses;
use Sheba\Partner\StatusChanger;
use Sheba\Subscription\Exceptions\AlreadyRunningSubscriptionRequestException;
use Sheba\Subscription\Exceptions\HasAlreadyCollectedFeeException;
use Sheba\Subscription\Partner\BillingType;
use Sheba\Subscription\Partner\PurchaseHandler;
use Throwable;

class PartnerSubscriptionController extends Controller
{
    use ModificationFields;

    /**
     * @param         $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function index($partner, Request $request)
    {
        try {
            /** @var Partner $partner */
            $partner = $request->partner;

            $partner_subscription_packages = $this->generateSubscriptionRelatedData($partner);
            $partner_subscription_package  = $partner->subscription;
            list($remaining, $wallet, $bonus_wallet, $threshold) = $partner->getCreditBreakdown();
            $data = [
                'subscription_package'       => $partner_subscription_packages,
                'monthly_tag'                => null, 'half_yearly_tag' => '১৯% ছাড়', 'yearly_tag' => '৫০% ছাড়',
                'tags'                       => [
                    'monthly'     => ['en' => null, 'bn' => null],
                    'half_yearly' => ['en' => '19% discount', 'bn' => '১৯% ছাড়'],
                    'yearly'      => ['en' => '50% discount', 'bn' => '৫০% ছাড়']
                ],
                'billing_type'               => $partner->billing_type,
                'current_package'            => [
                    'en' => $partner_subscription_package->show_name,
                    'bn' => $partner_subscription_package->show_name_bn
                ],
                'last_billing_date'          => $partner->last_billed_date ? $partner->last_billed_date->format('Y-m-d') : null,
                'next_billing_date'          => $partner->last_billed_date ? $partner->periodicBillingHandler()->nextBillingDate()->format('Y-m-d') : null,
                'validity_remaining_in_days' => $partner->last_billed_date ? $partner->periodicBillingHandler()->remainingDay() : null,
                'is_auto_billing_activated'  => ($partner->auto_billing_activated) ? true : false,
                'balance'                    => [
                    'wallet'                 => $wallet + $bonus_wallet,
                    'refund'                 => $remaining,
                    'minimum_wallet_balance' => $threshold
                ]
            ];

            return api_response($request, null, 200, $data);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllPackages(Request $request)
    {
        try {
            $data = [
                'subscription_package' => $this->generateSubscriptionRelatedData(),
                'monthly_tag'          => null, 'half_yearly_tag' => '১৯% ছাড়', 'yearly_tag' => '৫০% ছাড়',
                'tags'                 => [
                    'monthly'     => ['en' => null, 'bn' => null],
                    'half_yearly' => ['en' => '19% discount', 'bn' => '১৯% ছাড়'],
                    'yearly'      => ['en' => '50% discount', 'bn' => '৫০% ছাড়']
                ]
            ];

            return api_response($request, null, 200, $data);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param                            $rules
     * @param PartnerSubscriptionPackage $package
     * @return mixed
     */
    private function calculateDiscount($rules, PartnerSubscriptionPackage $package)
    {
        $rules['fee']['monthly']['original_price']   = $rules['fee']['monthly']['value'];
        $rules['fee']['monthly']['discount']         = $this->discountPrice($package, 'monthly');
        $monthly_discounted_price                    = $rules['fee']['monthly']['original_price'] - $rules['fee']['monthly']['discount'];
        $rules['fee']['monthly']['discounted_price'] = $monthly_discounted_price > 0 ? $monthly_discounted_price : 0;
        $rules['fee']['monthly']['discount_note']    = $this->discountNote($package, 'monthly');

        $rules['fee']['half_yearly']['original_price']             = $rules['fee']['half_yearly']['value'];
        $rules['fee']['half_yearly']['discount']                   = $this->discountPrice($package, 'half_yearly');
        $half_yearly_discounted_price                              = $rules['fee']['half_yearly']['original_price'] - $rules['fee']['half_yearly']['discount'];
        $rules['fee']['half_yearly']['discounted_price']           = $half_yearly_discounted_price > 0 ? $half_yearly_discounted_price : 0;
        $rules['fee']['half_yearly']['discount_note']              = $this->discountNote($package, 'half_yearly');
        $rules['fee']['half_yearly']['original_price_breakdown']   = round($rules['fee']['half_yearly']['original_price'] / 6, 2);
        $rules['fee']['half_yearly']['discounted_price_breakdown'] = round($rules['fee']['half_yearly']['discounted_price'] / 6, 2);
        $rules['fee']['half_yearly']['breakdown_type']             = 'monthly';

        $rules['fee']['yearly']['original_price']             = $rules['fee']['yearly']['value'];
        $rules['fee']['yearly']['discount']                   = $this->discountPrice($package, 'yearly');
        $yearly_discounted_price                              = $rules['fee']['yearly']['original_price'] - $rules['fee']['yearly']['discount'];
        $rules['fee']['yearly']['discounted_price']           = $yearly_discounted_price > 0 ? $yearly_discounted_price : 0;
        $rules['fee']['yearly']['discount_note']              = $this->discountNote($package, 'yearly');
        $rules['fee']['yearly']['original_price_breakdown']   = round($rules['fee']['yearly']['original_price'] / 12, 2);
        $rules['fee']['yearly']['discounted_price_breakdown'] = round($rules['fee']['yearly']['discounted_price'] / 12, 2);
        $rules['fee']['yearly']['breakdown_type']             = 'monthly';

        array_forget($rules, ['fee.monthly.value', 'fee.yearly.value']);

        return $rules;
    }

    /**
     * @param PartnerSubscriptionPackage $package
     * @param                            $billing_type
     * @return float|int
     */
    private function discountPrice(PartnerSubscriptionPackage $package, $billing_type)
    {
        if ($package->discounts->count()) {
            $partner_subscription_discount = $package->discounts->filter(function ($discount) use ($billing_type) {
                return $discount->billing_type == $billing_type;
            })->first();

            if ($partner_subscription_discount) {
                if (!$partner_subscription_discount->is_percentage) return (float)$partner_subscription_discount->amount; else {
                    return (float)$package->originalPrice($billing_type) * ($partner_subscription_discount->amount / 100);
                }
            }
            return 0;
        }
        return 0;
    }

    /**
     * @param PartnerSubscriptionPackage $package
     * @param                            $billing_type
     * @return string
     */
    private function discountNote(PartnerSubscriptionPackage $package, $billing_type)
    {
        if ($package->discounts->count()) {
            $partner_subscription_discount       = $package->discounts->filter(function ($discount) use ($billing_type) {
                return $discount->billing_type == $billing_type;
            })->first();
            $partner_subscription_discount_cycle = json_decode($partner_subscription_discount ? $partner_subscription_discount->applicable_billing_cycles : '[]');
            if (isset($partner_subscription_discount_cycle[0]) && $partner_subscription_discount_cycle[0] == 1) {
                $max_number = $this->hasSequence($partner_subscription_discount_cycle);
                return $max_number ? "First $max_number Billing Cycle" : $this->getOrdinalMessage($partner_subscription_discount_cycle);
            } elseif (count($partner_subscription_discount_cycle)) {
                return $this->getOrdinalMessage($partner_subscription_discount_cycle);
            }
            return '';
        }
        return '';
    }

    /**
     * @param $cycles
     * @return int
     */
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

    /**
     * @param $cycles
     * @return string
     */
    private function getOrdinalmessage($cycles)
    {
        $message = [];
        foreach ($cycles as $cycle) {
            $message[] = ordinal($cycle);
        }
        return implode(',', $message) . " Billing Cycle";
    }

    /**
     * @param         $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function store($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'package_id'    => 'required|numeric|exists:partner_subscription_packages,id',
                'billing_cycle' => 'required|string|in:monthly,yearly'
            ]);
            /** @var Partner $partner */
            $partner = $request->partner;
            $partner->subscribe((int)$request->package_id, $request->billing_cycle);

            if (isPartnerReadyToVerified($partner)) {
                $status_changer = new StatusChanger($request->partner, ['status' => PartnerStatuses::WAITING]);
                $status_changer->change();
            }

            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        try {
            /** @var Partner $partner */
            $partner = $request->partner;
            $this->validate($request, [
                'package_id' => 'required|numeric|exists:partner_subscription_packages,id', 'billing_type' => 'required|string|in:monthly,yearly'
            ]);

            if (((int)$request->package_id > (int)$partner->package_id) || ((int)$request->package_id == (int)$partner->package_id && $request->billing_type != $partner->billing_type && $partner->billing_type == 'monthly')) {
                return $this->purchase($request, true);
            } elseif (((int)$request->package_id == (int)$partner->package_id) && $request->billing_type == $partner->billing_type) {
                $partner_package = $partner->subscription;
                return api_response($request, null, 403, ['message' => "আপনি অলরেডি $partner_package->show_name_bn প্যাকেজে আছেন"]);
            } else {
                return $this->purchase($request, $partner, true);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param         $partner
     * @param bool    $inside
     * @return JsonResponse
     */
    public function purchase(Request $request, $partner, $inside = false)
    {
        try {
            $this->validate($request, [
                'package_id'   => 'required|numeric|exists:partner_subscription_packages,id',
                'billing_type' => 'required|string|in:' . implode(',', [BillingType::MONTHLY, BillingType::YEARLY, BillingType::HALF_YEARLY])
            ]);
            DB::beginTransaction();
            /** @var Partner $partner */
            $partner = $request->partner;
            /** @var PartnerSubscriptionPackage $requestedPackage */
            $requestedPackage = PartnerSubscriptionPackage::find($request->package_id);
            if (empty($requestedPackage)) {
                return api_response($request, null, 403, ['message' => 'আপনার অনুরধক্রিত প্যকেজটি পাওয়া যায় নাই']);
            }
            $handler = (new PurchaseHandler($partner))->setModifier($request->manager_resource)->setNewBillingType($request->billing_type)->setNewPackage($requestedPackage);
            $handler->checkIfRunningAndAlreadyCollected();
            /** @var PartnerSubscriptionUpdateRequest $upgradeRequest */
            $upgradeRequest = $handler->getSubscriptionRequest();
            if (empty($upgradeRequest)) {
                try {
                    $grade = $handler->getGrade();
                    if ($grade == PartnerSubscriptionChange::DOWNGRADE && $partner->status != PartnerStatuses::INACTIVE) {
                        return api_response($request, null, $inside ? 200 : 202, ['message' => " আপনার $requestedPackage->show_name_bd  প্যকেজে অবনমনের  অনুরোধ  গ্রহণ  করা  হয়েছে "]);
                    }
                    $hasCredit = $handler->hasCredit();
                    if (!$hasCredit) {
                        DB::rollback();
                        $handler->notifyForInsufficientBalance();
                        return api_response($request, null, $inside ? 403 : 420, array_merge(['message' => 'আপনার একাউন্টে যথেষ্ট ব্যলেন্স নেই।।', 'required' => $handler->getRequiredBalance()], $handler->getBalance()));
                    }
                    $handler->purchase();
                    if ($grade === PartnerSubscriptionChange::RENEWED) {
                        DB::commit();
                        return api_response($request, null, 200, array_merge(['message' => "আপনাকে $requestedPackage->show_name_bn প্যকেজে পুনর্বহাল করা হয়েছে।"], $handler->getBalance()));
                    } else {
                        DB::commit();
                        return api_response($request, null, 200, array_merge(['message' => "আপনাকে $requestedPackage->show_name_bn প্যকেজে উন্নীত করা হয়েছে।"], $handler->getBalance()));
                    }
                } catch (Throwable $e) {
                    DB::rollback();
                    throw $e;
                }
            } else {
                DB::rollback();
                return api_response($request, null, 403, ['message' => "$requestedPackage->show_name_bn প্যাকেজে যেতে অনুগ্রহ করে সেবার সাথে যোগাযোগ করুন"]);
            }

        } catch (AlreadyRunningSubscriptionRequestException $e) {
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        } catch (HasAlreadyCollectedFeeException $e) {
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function createSubscriptionRequest(PartnerSubscriptionPackage $requested_package)
    {
        $request          = \request();
        $partner          = $request->partner;
        $running_discount = $requested_package->runningDiscount($request->billing_type);
        $this->setModifier($request->manager_resource);
        $update_request_data = $this->withCreateModificationField([
            'partner_id' => $partner->id, 'old_package_id' => $partner->package_id ?: 1, 'new_package_id' => $request->package_id, 'old_billing_type' => $partner->billing_type ?: BillingType::MONTHLY, 'new_billing_type' => $request->billing_type, 'discount_id' => $running_discount ? $running_discount->id : null
        ]);
        return PartnerSubscriptionUpdateRequest::create($update_request_data);
    }

    public function toggleAutoBillingActivation(Request $request)
    {
        $request->partner->auto_billing_activated = $request->partner->auto_billing_activated == 1 ? 0 : 1;
        $request->partner->save();
        $task = $request->partner->auto_billing_activated ? 'activated' : 'deactivated';
        return api_response($request, null, 200, ['message' => "Billing auto renewal $task", 'auto_billing_activated' => $request->partner->auto_billing_activated]);
    }

    /**
     * @param Partner $partner | null
     * @return mixed
     */
    private function generateSubscriptionRelatedData(Partner $partner = null)
    {
        $featured_package_id           = config('partner.subscription_featured_package_id');
        $partner_subscription_packages = PartnerSubscriptionPackage::validDiscounts()
                                                                   ->select('id', 'name', 'name_bn', 'show_name', 'show_name_bn', 'tagline', 'tagline_bn', 'rules', 'usps', 'badge', 'features')
                                                                   ->whereIn('id', constants('PARTNER_SHOWABLE_PACKAGE'))
                                                                   ->get();

        foreach ($partner_subscription_packages as $package) {
            $package['rules']        = $this->calculateDiscount(json_decode($package->rules, 1), $package);
            $package['is_published'] = $package->name == 'LITE' ? 0 : 1;
            $package['usps']         = $package->usps ? json_decode($package->usps) : ['usp' => [], 'usp_bn' => []];
            $package['features']     = $package->features ? json_decode($package->features) : [];
            $package['is_featured']  = in_array($package->id, $featured_package_id);

            if ($partner) {
                $package['is_subscribed']     = (int)($partner->package_id == $package->id);
                $package['subscription_type'] = ($partner->package_id == $package->id) ? $partner->billing_type : null;
            }
            removeRelationsAndFields($package);
        }

        return $partner_subscription_packages;
    }
}
