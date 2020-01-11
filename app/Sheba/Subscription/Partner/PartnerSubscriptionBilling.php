<?php namespace Sheba\Subscription\Partner;

use App\Jobs\PartnerRenewalSMS;
use App\Models\Partner;
use App\Models\PartnerStatusChangeLog;
use App\Models\PartnerSubscriptionPackage;
use App\Models\Tag;
use App\Repositories\NotificationRepository;
use App\Repositories\SmsHandler;
use App\Sheba\Subscription\Partner\PartnerSubscriptionChange;
use App\Sheba\Subscription\Partner\PartnerSubscriptionCharges;
use Carbon\Carbon;
use DB;
use Exception;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\ModificationFields;
use Sheba\Partner\PartnerStatuses;
use Sheba\PartnerWallet\PartnerTransactionHandler;
use Sheba\PartnerWallet\PaymentByBonusAndWallet;

class PartnerSubscriptionBilling
{
    use ModificationFields;

    /** @var Partner $partner */
    public $partner;
    public $runningCycleNumber;
    private $partnerTransactionHandler;
    public $partnerBonusHandler;
    public $today;
    public $refundAmount;
    public $packagePrice;
    public $packageFrom;
    public $packageTo;
    private $isCollectAdvanceSubscriptionFee = false;

    /**
     * PartnerSubscriptionBilling constructor.
     * @param Partner $partner
     */
    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
        $this->partnerTransactionHandler = new PartnerTransactionHandler($this->partner);
        $this->partnerBonusHandler = new PaymentByBonusAndWallet($this->partner, $this->partner->subscription);
        $this->today = Carbon::today();
        $this->refundAmount = 0;
        $this->isCollectAdvanceSubscriptionFee = $this->partner->isAlreadyCollectedAdvanceSubscriptionFee();
    }

    public function runUpfrontBilling()
    {
        $this->runningCycleNumber = 1;
        $this->partner->billing_start_date = $this->today;
        $this->packagePrice = $this->getSubscribedPackageDiscountedPrice();
        $this->billingDatabaseTransactions($this->packagePrice);
        if (!$this->isCollectAdvanceSubscriptionFee) {
            (new PartnerSubscriptionCharges($this))->shootLog(constants('PARTNER_PACKAGE_CHARGE_TYPES')[PartnerSubscriptionChange::RENEWED]);
        }
    }

    public function runSubscriptionBilling()
    {
        $this->runningCycleNumber = $this->calculateRunningBillingCycleNumber();
        $this->packagePrice = $this->getSubscribedPackageDiscountedPrice();
        $this->billingDatabaseTransactions($this->packagePrice);
        if (!$this->isCollectAdvanceSubscriptionFee) {
            (new PartnerSubscriptionCharges($this))->shootLog(constants('PARTNER_PACKAGE_CHARGE_TYPES')[PartnerSubscriptionChange::RENEWED]);
        }
        dispatch((new PartnerRenewalSMS($this->partner))->setPackage($this->partner->subscription)->setSubscriptionAmount($this->packagePrice));
    }

    /**
     * @param PartnerSubscriptionPackage $old_package
     * @param PartnerSubscriptionPackage $new_package
     * @param $old_billing_type
     * @param $new_billing_type
     * @param $discount_id
     * @throws Exception
     */
    public function runUpgradeBilling(PartnerSubscriptionPackage $old_package, PartnerSubscriptionPackage $new_package, $old_billing_type, $new_billing_type, $discount_id)
    {
        $discount = 0;
        $this->packageFrom = $old_package;
        $this->packageTo = $new_package;
        $remaining_credit = $this->remainingCredit($old_package, $old_billing_type);
        if ($discount_id) $discount = $new_package->discountPriceFor($discount_id);
        $this->packagePrice = ($new_package->originalPrice($new_billing_type) - $discount) - $remaining_credit;
        if ($this->packagePrice < 0) {
            $this->refundRemainingCredit(abs($this->packagePrice));
            $this->packagePrice = 0;
        }
        $grade = $this->findGrade($new_package, $old_package, $new_billing_type, $old_billing_type);
        if (in_array($grade, [PartnerSubscriptionChange::UPGRADE, PartnerSubscriptionChange::DOWNGRADE]) || !$this->partner->billing_start_date) {
            $this->partner->billing_start_date = $this->today;
        }

        $this->billingDatabaseTransactions($this->packagePrice);
        if (!$this->isCollectAdvanceSubscriptionFee) {
            (new PartnerSubscriptionCharges($this))->shootLog(PartnerSubscriptionChange::all()[$grade]);
        }
        $this->sendSmsForSubscriptionUpgrade($old_package, $new_package, $old_billing_type, $new_billing_type, $grade);
        $this->storeEntry();
    }

    public function runAdvanceSubscriptionBilling()
    {
        $this->runningCycleNumber = $this->calculateRunningBillingCycleNumber();
        $this->packagePrice = $this->getSubscribedPackageDiscountedPrice();
        $this->advanceBillingDatabaseTransactions($this->packagePrice);
        (new PartnerSubscriptionCharges($this))->shootLog(constants('PARTNER_PACKAGE_CHARGE_TYPES')[PartnerSubscriptionChange::RENEWED]);
    }

    private function calculateRunningBillingCycleNumber()
    {
        if (!$this->partner->billing_start_date) return 1;
        if ($this->partner->billing_type == BillingType::MONTHLY) {
            $diff = $this->today->month - $this->partner->billing_start_date->month;
            $yearDiff = ($this->today->year - $this->partner->billing_start_date->year);
            return $diff + ($yearDiff * 12) + 1;
        } elseif ($this->partner->billing_type == BillingType::HALF_YEARLY) {
            $month_diff = $this->today->diffInMonths($this->partner->billing_start_date);
            return (int)($month_diff / 6) + 1;
        } elseif ($this->partner->billing_type == BillingType::YEARLY) {
            return ($this->today->year - $this->partner->billing_start_date->year) + 1;
        }
    }

    private function getSubscribedPackageDiscountedPrice()
    {
        $original_price = $this->partner->subscription->originalPrice($this->partner->billing_type);
        $discount = $this->calculateSubscribedPackageDiscount($this->runningCycleNumber, $original_price);
        return $original_price - $discount;
    }

    /**
     * @param $package_price
     */
    private function billingDatabaseTransactions($package_price)
    {
        DB::transaction(function () use ($package_price) {
            if (!$this->isCollectAdvanceSubscriptionFee) {
                $this->partnerTransactionForSubscriptionBilling($package_price);
            }
            $this->partner->last_billed_date = $this->today;
            $this->partner->last_billed_amount = $this->getSubscribedPackageDiscountedPrice();
            if ($this->partner->status == PartnerStatuses::INACTIVE) {
                $this->revokeStatus();
            }
            $this->partner->update();
        });
    }

    private function revokeStatus()
    {
        $log = PartnerStatusChangeLog::query()->where([
            ['partner_id', $this->partner->id],
            ['reason', 'Subscription Expired'],
            ['to', PartnerStatuses::INACTIVE],
            ['from', '!=', PartnerStatuses::INACTIVE]
        ])->orderBy('created_at', 'DESC')->first();

        if ($log) {
            $this->partner->status = $log->from;
            $status_change_log = [
                'from' => $log->to,
                'to' => $log->from,
                'reason' => 'Subscription Revoked',
                'log' => 'Partner became active due to subscription purchase'
            ];

            $this->partner->statusChangeLogs()->create($this->withCreateModificationField($status_change_log));
        }
    }

    /**
     * @param $package_price
     */
    private function advanceBillingDatabaseTransactions($package_price)
    {
        DB::transaction(function () use ($package_price) {
            $this->partnerTransactionForSubscriptionBilling($package_price);
        });
    }

    /**
     * @param $package_price
     * @throws Exception
     */
    private function partnerTransactionForSubscriptionBilling($package_price)
    {
        $package_price = number_format($package_price, 2, '.', '');
        $this->partnerBonusHandler->pay($package_price, '%d BDT has been deducted for subscription package', [$this->getSubscriptionTag()->id]);
    }

    /**
     * @param PartnerSubscriptionPackage $old_package
     * @param $old_billing_type
     * @return string
     */
    public function remainingCredit(PartnerSubscriptionPackage $old_package, $old_billing_type)
    {
        $dayDiff = $this->partner->last_billed_date ? $this->partner->last_billed_date->diffInDays($this->today) + 1 : 0;
        $used_credit = $old_package->originalPricePerDay($old_billing_type) * $dayDiff;
        $remaining_credit = ($this->partner->last_billed_amount ?: 0) - $used_credit;
        $alreadyCollectedSubscriptionFee = $this->partner->alreadyCollectedSubscriptionFee();
        $remaining_credit += $alreadyCollectedSubscriptionFee;
        return $remaining_credit < 0 ? 0 : round($remaining_credit, 2);
    }

    /**
     * @param $refund_amount
     * @throws Exception
     */
    private function refundRemainingCredit($refund_amount)
    {
        $refund_amount = number_format($refund_amount, 2, '.', '');
        $this->partnerTransactionHandler->credit($refund_amount, $refund_amount . ' BDT has been refunded due to subscription package upgrade', null, [$this->getSubscriptionTag()->id]);
        $this->refundAmount = $refund_amount;
    }

    /**
     * @param $running_bill_cycle_no
     * @param $original_price
     * @return float|int
     */
    private function calculateSubscribedPackageDiscount($running_bill_cycle_no, $original_price)
    {
        if ($this->partner->discount_id) {
            $subscription_discount = $this->partner->subscriptionDiscount;
            $discount_billing_cycles = json_decode($subscription_discount->applicable_billing_cycles);
            if (empty($discount_billing_cycles) || in_array($running_bill_cycle_no, $discount_billing_cycles)) {
                if ($subscription_discount->is_percentage) {
                    return $original_price * ($subscription_discount->amount / 100);
                } else {
                    return (double)$subscription_discount->amount;
                }
            }
        }
        return 0;
    }

    private function getSubscriptionTag()
    {
        return Tag::where('name', 'Subscription fee')->where('taggable_type', 'App\\Models\\PartnerTransaction')->first();
    }

    /**
     * @param PartnerSubscriptionPackage $old_package
     * @param PartnerSubscriptionPackage $new_package
     * @param $old_billing_type
     * @param $new_billing_type
     * @param string $grade
     * @throws Exception
     */
    private function sendSmsForSubscriptionUpgrade(PartnerSubscriptionPackage $old_package, PartnerSubscriptionPackage $new_package, $old_billing_type, $new_billing_type, $grade = PartnerSubscriptionChange::UPGRADE)
    {
        if ((int)env('PARTNER_SUBSCRIPTION_SMS') == 1) {
            $template = null;
            if ($grade == PartnerSubscriptionChange::UPGRADE) {
                $template = 'upgrade-subscription';
            } elseif ($grade == PartnerSubscriptionChange::RENEWED) {
                $template = 'renew-subscription';
            } elseif ($grade == PartnerSubscriptionChange::DOWNGRADE) {
                $template = 'downgrade-subscription';
            }
            if ($template) {
                self::sendSms($this->partner, $old_package, $new_package, $old_billing_type, $new_billing_type, $this->packagePrice, $template);
                self::sendNotification($this->partner, $old_package, $new_package, $old_billing_type, $new_billing_type, $this->packagePrice, $grade);
            }
        }
    }

    /**
     * @param $new
     * @param $old
     * @param $new_billing_type
     * @param $old_billing_type
     * @return string
     */
    public function findGrade($new, $old, $new_billing_type, $old_billing_type)
    {
        if ($old->id < $new->id) {
            return PartnerSubscriptionChange::UPGRADE;
        } else if ($old->id > $new->id) {
            return PartnerSubscriptionChange::DOWNGRADE;
        } else {
            $types = [BillingType::MONTHLY, BillingType::HALF_YEARLY, BillingType::YEARLY];
            $old_type_index = array_search($old_billing_type, $types);
            $new_type_index = array_search($new_billing_type, $types);
            if ($old_type_index < $new_type_index) {
                return PartnerSubscriptionChange::UPGRADE;
            } elseif ($old_type_index > $new_type_index) {
                return PartnerSubscriptionChange::DOWNGRADE;
            } else {
                return PartnerSubscriptionChange::RENEWED;
            }
        }
    }

    /**
     * @param Partner $partner
     * @param $old_package
     * @param $new_package
     * @param $old_billing_type
     * @param $new_billing_type
     * @param $price
     * @param $grade
     */
    public static function sendNotification(Partner $partner, $old_package, $new_package, $old_billing_type, $new_billing_type, $price, $grade)
    {
        $title = '';
        $message = '';
        $type_text = BillingType::BN()[$new_billing_type];
        $fee = convertNumbersToBangla(floatval($price));
        switch ($grade) {
            case PartnerSubscriptionChange::UPGRADE:
                $title = "সাবস্ক্রিপশন সম্পন্ন";
                $message = " আপনি এসম্যানেজার এর $type_text $new_package->show_name_bn প্যকেজ এ সফল ভাবে সাবস্ক্রিপশন সম্পন্ন করেছেন। সাবস্ক্রিপশন ফি বাবদ $fee  টাকা চার্জ করা হয়েছে।";
                break;
            case PartnerSubscriptionChange::RENEWED:
                $title = "সাবস্ক্রিপশন  নবায়ন";
                $message = "আপনি এসম্যানেজার এর $type_text $new_package->show_name_bn প্যকেজ এ সফল ভাবে সাবস্ক্রিপশন নবায়ন করেছেন। সাবস্ক্রিপশন ফি বাবদ $fee টাকা চার্জ করা হয়েছে।";
                break;
            case PartnerSubscriptionChange::DOWNGRADE:
                return;
        }
        (new NotificationRepository())->sendSubscriptionNotification($title, $message, $partner);
    }

    /**
     * @param Partner $partner
     * @param $old_package
     * @param $new_package
     * @param $old_billing_type
     * @param $new_billing_type
     * @param $price
     * @param $template
     * @throws Exception
     */
    public static function sendSms(Partner $partner, $old_package, $new_package, $old_billing_type, $new_billing_type, $price, $template)
    {
        (new SmsHandler($template))->send($partner->getContactNumber(), [
            'old_package_name' => $old_package->show_name_bn,
            'new_package_name' => $new_package->show_name_bn,
            'subscription_amount' => $price,
            'old_package_type' => $old_billing_type,
            'new_package_type' => $new_billing_type,
            'package_name' => $new_package->show_name_bn,
            'formatted_package_type' => $new_billing_type == BillingType::MONTHLY ? 'মাসের' : $new_billing_type == BillingType::YEARLY ? 'বছরের' : 'আর্ধবছরের',
            'package_type' => $new_billing_type
        ]);
    }

    private function storeEntry()
    {
        /**
         * Expense Entry for subscription
         *
         * @var AutomaticEntryRepository $entry
         */
        $entry = app(AutomaticEntryRepository::class);
        $entry->setPartner($this->partner)->setHead(AutomaticExpense::SUBSCRIPTION_FEE)->setAmount($this->packagePrice)->store();
    }
}
